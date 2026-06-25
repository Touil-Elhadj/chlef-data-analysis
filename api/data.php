<?php
error_reporting(E_ERROR);
require_once '../config.php';
require_once '../lang.php';
$session = checkSession();
$action  = $_GET['action']??'stats';
$db      = getDB();

// Helper : permission lecture (admin OU invité)
$canRead = isAdmin() || isGuest();
// Permission écriture : seuls les admins (les invités sont bloqués)
$canModify = isAdmin() && !isGuest();

if ($action==='stats'){
    $total = $db->query('SELECT COUNT(*) FROM responses')->fetchColumn();
    $today = $db->query("SELECT COUNT(*) FROM responses WHERE DATE(entered_at)=CURDATE()")->fetchColumn();
    $bmi   = $db->query('SELECT ROUND(AVG(bmi),1) FROM responses WHERE bmi IS NOT NULL')->fetchColumn();
    $obese = $db->query("SELECT COUNT(*) FROM responses WHERE iotf_class='Obésité'")->fetchColumn();
    $pct   = $total>0?round($obese/$total*100,1):null;
    jsonResponse(['success'=>true,'total'=>(int)$total,'today'=>(int)$today,'bmi_avg'=>$bmi,'obese_pct'=>$pct]);
}

if ($action==='list'){
    $page=(int)($_GET['page']??1); $limit=50; $offset=($page-1)*$limit;
    $q='%'.($_GET['q']??'').'%';
    $stmt=$db->prepare("SELECT r.id,r.questionnaire_num,r.age,r.sex,r.grade,r.height,r.weight,r.bmi,r.iotf_class,r.skip_meal_tutoring,r.parent_obese,r.academic_stress,r.global_nutrition_score,r.global_nutrition_class,r.obesity_risk_class,r.entered_at,u.full_name as entered_by_name FROM responses r LEFT JOIN users u ON r.entered_by=u.id WHERE r.questionnaire_num LIKE ? OR r.iotf_class LIKE ? OR r.sex LIKE ? ORDER BY r.questionnaire_num ASC LIMIT ? OFFSET ?");
    $stmt->execute([$q,$q,$q,$limit,$offset]);
    $cnt=$db->prepare("SELECT COUNT(*) FROM responses WHERE questionnaire_num LIKE ? OR iotf_class LIKE ? OR sex LIKE ?");
    $cnt->execute([$q,$q,$q]);
    jsonResponse(['success'=>true,'data'=>$stmt->fetchAll(),'total'=>(int)$cnt->fetchColumn(),'page'=>$page]);
}

if ($action==='delete' && $canModify){
    $id=(int)($_GET['id']??0);
    if ($id>0){
        $rec=$db->prepare('SELECT questionnaire_num FROM responses WHERE id=?');
        $rec->execute([$id]); $r=$rec->fetch();
        $db->prepare('DELETE FROM responses WHERE id=?')->execute([$id]);
        auditLog('DELETE','responses',$id,'Q#'.($r['questionnaire_num']??'?'));
        jsonResponse(['success'=>true]);
    }
    jsonResponse(['success'=>false,'message'=>'ID invalide']);
}
if ($action==='delete' && isGuest()) jsonResponse(['success'=>false,'message'=>__('guest_cannot_action')]);

if ($action==='validate' && $canModify){
    $id=(int)($_GET['id']??0); $v=(int)($_GET['v']??1);
    $db->prepare('UPDATE responses SET is_validated=? WHERE id=?')->execute([$v,$id]);
    auditLog('UPDATE','responses',$id,"Validation: $v");
    jsonResponse(['success'=>true]);
}
if ($action==='validate' && isGuest()) jsonResponse(['success'=>false,'message'=>__('guest_cannot_action')]);

if ($action==='export_csv' && $canRead){
    $rows=$db->query('SELECT * FROM responses ORDER BY questionnaire_num')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="questionnaire_chlef_'.date('Y-m-d').'.csv"');
    $out=fopen('php://output','w');
    fprintf($out,chr(0xEF).chr(0xBB).chr(0xBF));
    if($rows){ fputcsv($out,array_keys($rows[0])); foreach($rows as $r) fputcsv($out,$r); }
    fclose($out);
    auditLog('EXPORT','responses',null,'CSV '.count($rows).' lignes');
    exit;
}

if ($action==='export_spss' && $canRead){
    // Export format SPSS-compatible (CSV avec métadonnées)
    $rows=$db->query('SELECT * FROM responses ORDER BY questionnaire_num')->fetchAll();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="questionnaire_chlef_'.date('Y-m-d').'_SPSS.csv"');
    $out=fopen('php://output','w');
    fprintf($out,chr(0xEF).chr(0xBB).chr(0xBF));

    // En-têtes numériques pour SPSS (sans espaces)
    if($rows){
        $headers=array_keys($rows[0]);
        fputcsv($out,$headers,',','"');
        foreach($rows as $r){
            // Convertir les valeurs catégorielles en numériques pour SPSS
            $r['sex']=$r['sex']==='Garçon'?1:2;
            $r['delivery_type']=$r['delivery_type']==='Césarienne'?2:1;
            $r['sports_club']=$r['sports_club']==='Oui'?1:0;
            $r['parent_obese']=$r['parent_obese']==='Oui'?1:0;
            fputcsv($out,$r,',','"');
        }
    }
    fclose($out);
    auditLog('EXPORT','responses',null,'SPSS '.count($rows).' lignes');
    exit;
}

if ($action==='audit' && $canRead){
    $page=(int)($_GET['page']??1); $limit=50; $offset=($page-1)*$limit;
    $stmt=$db->prepare("SELECT a.*,u.full_name FROM audit_log a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit,$offset]);
    jsonResponse(['success'=>true,'data'=>$stmt->fetchAll(),'total'=>(int)$db->query("SELECT COUNT(*) FROM audit_log")->fetchColumn()]);
}

jsonResponse(['success'=>false,'message'=>'Action inconnue']);
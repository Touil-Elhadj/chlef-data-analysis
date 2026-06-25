<?php

declare(strict_types=1);

namespace TouilElhadj\ChlefPlatform;

/**
 * Chart-data generator for the Chlef adolescent-overweight study.
 *
 * Produces JSON-ready datasets for Chart.js consumption, covering
 * BMI distribution by sex, IOTF prevalence stacks, KIDMED-vs-BMI
 * scatter plots, and the correlation matrix of behavioural variables.
 *
 * This class is study-specific by design — it makes assumptions about
 * the column names (`bmi`, `sex`, `iotf_class`, `score_kidmed`, etc.)
 * that match the Chlef survey schema. It is NOT part of the generic
 * `biostat-php` library.
 *
 * @author  Elhadj TOUIL <touilelhadj@live.com>
 * @license MIT
 */
class ChartDataGenerator {
    
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Distribution IMC par sexe (violin plot data)
     */
    public function bmiDistributionBySex() {
        $boys = [];
        $girls = [];
        
        foreach($this->data as $r) {
            if(isset($r['bmi']) && is_numeric($r['bmi'])) {
                if($r['sex'] == 'Garçon') {
                    $boys[] = (float)$r['bmi'];
                } else {
                    $girls[] = (float)$r['bmi'];
                }
            }
        }
        
        return [
            'labels' => ['Garçons', 'Filles'],
            'datasets' => [
                [
                    'label' => 'Garçons',
                    'data' => $boys,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)'
                ],
                [
                    'label' => 'Filles',
                    'data' => $girls,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)'
                ]
            ]
        ];
    }
    
    /**
     * Prévalence IOTF par sexe (stacked bar)
     */
    public function iotfPrevalenceBySex() {
        $categories = ['Minceur grade 3', 'Minceur grade 2', 'Minceur', 
                      'Normal', 'Surpoids', 'Obésité'];
        
        $boys = array_fill_keys($categories, 0);
        $girls = array_fill_keys($categories, 0);
        
        foreach($this->data as $r) {
            if(!isset($r['iotf_class'])) continue;
            
            if($r['sex'] == 'Garçon') {
                $boys[$r['iotf_class']]++;
            } else {
                $girls[$r['iotf_class']]++;
            }
        }
        
        $boys_total = array_sum($boys);
        $girls_total = array_sum($girls);
        
        // Convertir en pourcentages
        foreach($boys as &$v) $v = $boys_total > 0 ? $v / $boys_total * 100 : 0;
        foreach($girls as &$v) $v = $girls_total > 0 ? $v / $girls_total * 100 : 0;
        
        return [
            'labels' => ['Garçons', 'Filles'],
            'datasets' => array_map(function($cat) use ($boys, $girls) {
                return [
                    'label' => $cat,
                    'data' => [$boys[$cat], $girls[$cat]]
                ];
            }, $categories)
        ];
    }
    
    /**
     * Matrice de corrélation (heatmap data)
     */
    public function correlationMatrix() {
        $vars = ['bmi', 'score_kidmed', 'score_activite', 'score_sedentarite', 'score_sommeil'];
        $matrix = [];
        
        $stats = new BiostatAnalysis($this->data);
        
        foreach($vars as $v1) {
            $row = [];
            foreach($vars as $v2) {
                if($v1 == $v2) {
                    $row[] = 1.0;
                } else {
                    $x = array_column($this->data, $v1);
                    $y = array_column($this->data, $v2);
                    $corr = $stats->pearson($x, $y);
                    $row[] = $corr['r'];
                }
            }
            $matrix[] = $row;
        }
        
        return [
            'labels' => $vars,
            'data' => $matrix
        ];
    }
}

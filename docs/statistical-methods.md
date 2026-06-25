# Statistical Methods

This document specifies the mathematical formulations and bibliographic
references for every public method of `BiostatPHP::BiostatAnalysis`.
Reviewers can verify the implementation against the reference values
computed in R / SPSS and listed in `tests/run_tests.php`.

> **Notation.** $y$ is the outcome, $X$ is the $n \times p$ design matrix,
> $\beta$ is the coefficient vector, $\hat{\beta}$ its estimate,
> $\boldsymbol{1}_A$ is the indicator function of event $A$.

---

## 1. Descriptive statistics

| Method | Formula |
|---|---|
| `mean` | $\bar{x} = \frac{1}{n}\sum_{i=1}^{n} x_i$ |
| `std` | $s = \sqrt{\frac{1}{n-1}\sum_{i=1}^{n} (x_i - \bar{x})^2}$ |
| `median` | order statistic $x_{((n+1)/2)}$ (odd $n$) or mean of two middle values (even $n$) |
| `quantile(p)` | linear interpolation between consecutive order statistics (R `type = 7`) |
| `skewness` | $\frac{1}{n}\sum_{i} \left(\frac{x_i - \bar{x}}{s}\right)^3$ |
| `kurtosis` | $\frac{1}{n}\sum_{i} \left(\frac{x_i - \bar{x}}{s}\right)^4 - 3$ (excess) |

---

## 2. Categorical comparisons

### 2.1 χ² for 2 × 2 tables — `chiSquare`

For an observed table with counts $O_{ij}$ and expected counts $E_{ij}$:

$$
\chi^2 = \sum_{i,j} \frac{(O_{ij} - E_{ij})^2}{E_{ij}}, \qquad
\text{df} = (r-1)(c-1).
$$

The Yates' continuity correction is applied automatically when any expected
count is below 5:

$$
\chi^2_{\text{Yates}} = \sum_{i,j} \frac{(|O_{ij} - E_{ij}| - 0.5)^2}{E_{ij}}.
$$

The *p*-value is computed from the upper-tail χ² CDF using the
regularised incomplete gamma function $\gamma(\nu/2,\,\chi^2/2)$.

### 2.2 Fisher's exact test — `fisherExact`

Exact hypergeometric *p*-value for a 2 × 2 table $(a, b, c, d)$:

$$
P = \sum_{k} \frac{\binom{a+b}{k}\binom{c+d}{a+c-k}}{\binom{n}{a+c}},
$$

summed over tables more extreme than (or equal to) the observed one.

### 2.3 Odds ratio with 95 % CI — `oddsRatio`

$$
\widehat{OR} = \frac{a d}{b c}, \qquad
\widehat{SE}(\ln \widehat{OR}) = \sqrt{\tfrac{1}{a}+\tfrac{1}{b}+\tfrac{1}{c}+\tfrac{1}{d}},
$$

$$
\text{CI}_{95\%} = \exp\bigl(\ln\widehat{OR} \pm 1.96 \cdot \widehat{SE}\bigr).
$$

Haldane–Anscombe continuity correction (+ 0.5 to every cell) is applied
automatically when any cell is zero.

---

## 3. Comparison of means

### 3.1 Welch's *t*-test — `welchT`

For two independent samples with unequal variances:

$$
t = \frac{\bar{x}_1 - \bar{x}_2}{\sqrt{s_1^2/n_1 + s_2^2/n_2}}, \qquad
\nu = \frac{(s_1^2/n_1 + s_2^2/n_2)^2}{\frac{(s_1^2/n_1)^2}{n_1-1} + \frac{(s_2^2/n_2)^2}{n_2-1}}.
$$

The *p*-value is from the two-sided Student-*t* distribution with $\nu$ df.

### 3.2 One-way ANOVA — `anova`

$$
F = \frac{\text{MS}_{\text{between}}}{\text{MS}_{\text{within}}} =
\frac{\sum_g n_g (\bar{y}_g - \bar{y})^2 / (k-1)}
     {\sum_g (n_g - 1) s_g^2 / (n-k)}, \qquad
\text{df}_1 = k - 1, \; \text{df}_2 = n - k.
$$

The *p*-value is from the regularised incomplete beta function.

---

## 4. Correlation

| Method | Formula |
|---|---|
| Pearson `pearson` | $r = \dfrac{\sum (x_i-\bar{x})(y_i-\bar{y})}{\sqrt{\sum (x_i-\bar{x})^2 \sum (y_i-\bar{y})^2}}$ |
| Spearman `spearman` | Pearson correlation of the ranks of $x$ and $y$, with mid-rank treatment of ties |

For both, the *p*-value is computed from $t = r\sqrt{(n-2)/(1-r^2)}$ on
$n-2$ df.

---

## 5. Logistic regression — `logisticRegression`

The model is

$$
\Pr(Y_i = 1 \mid x_i) = \sigma(x_i^\top \beta) = \frac{1}{1 + e^{-x_i^\top \beta}}.
$$

Estimation is by **Newton–Raphson iteration on the score equation**:

$$
\beta^{(k+1)} = \beta^{(k)} + (X^\top W X)^{-1} X^\top (y - \mu^{(k)}),
$$

where $W = \mathrm{diag}\bigl(\mu^{(k)}_i (1 - \mu^{(k)}_i)\bigr)$. The
iteration stops when $\max_j |\beta^{(k+1)}_j - \beta^{(k)}_j| < 10^{-6}$ or
after 50 iterations. The covariance matrix of $\hat\beta$ is $(X^\top W X)^{-1}$;
standard errors are its diagonal square roots.

### 5.1 AUC

Computed as the Mann–Whitney $U$ statistic on the predicted probabilities,
equivalent to the area under the empirical ROC curve.

### 5.2 Hosmer–Lemeshow goodness-of-fit

The data are divided into $g = 10$ deciles of predicted probability; the
statistic

$$
H = \sum_{j=1}^{g} \frac{(O_j - E_j)^2}{E_j (1 - E_j / n_j)}
$$

is referred to a $\chi^2$ with $g - 2$ df.

---

## 6. Multiple testing — Benjamini–Hochberg FDR

Given $m$ ordered *p*-values $p_{(1)} \leq \cdots \leq p_{(m)}$, the BH
adjusted *p*-value is

$$
\tilde{p}_{(i)} = \min_{j \geq i} \frac{m\, p_{(j)}}{j}.
$$

Rejection at FDR level $q$: reject $H_{0,(i)}$ for all $i \leq i^*$ where
$i^* = \max\{ i : p_{(i)} \leq i q / m \}$.

**Reference**: Benjamini & Hochberg, *JRSSB* 57(1):289–300, 1995.

---

## 7. Variance Inflation Factor — `vif`

For each predictor $X_j$, fit the auxiliary OLS regression of $X_j$ on the
other predictors, obtain $R_j^2$, and compute

$$
\mathrm{VIF}_j = \frac{1}{1 - R_j^2}.
$$

Thresholds used (Allison 2012): VIF > 2.5 = noticeable multicollinearity,
VIF > 5 = problematic.

---

## 8. Box–Tidwell test — `boxTidwell`

For continuous predictors $X_j$, add the term $X_j \ln(X_j)$ to a logistic
regression including the original $X_j$ and the other covariates. The Wald
test on the coefficient of $X_j \ln(X_j)$ assesses departure from linearity
of the logit in $X_j$.

**Reference**: Box & Tidwell, *Technometrics* 4(4):531–550, 1962.

---

## 9. GLMM (logistic, random intercept) — `glmmLogistic`

Model:

$$
\Pr(Y_{ij} = 1 \mid x_{ij}, u_i) = \sigma(x_{ij}^\top \beta + u_i), \qquad
u_i \sim \mathcal{N}(0, \sigma_u^2).
$$

Estimation is by **Penalised Quasi-Likelihood (PQL)** using Henderson's
mixed-model equations. At each iteration the working response is

$$
y_{ij}^* = \eta_{ij}^{(k)} + \frac{y_{ij} - \mu_{ij}^{(k)}}{\mu_{ij}^{(k)}(1 - \mu_{ij}^{(k)})},
$$

and Henderson's equations

$$
\begin{pmatrix} X^\top W X & X^\top W Z \\ Z^\top W X & Z^\top W Z + \sigma_u^{-2} I \end{pmatrix}
\begin{pmatrix} \beta \\ u \end{pmatrix}
= \begin{pmatrix} X^\top W y^* \\ Z^\top W y^* \end{pmatrix}
$$

are solved. $\sigma_u^2$ is updated by ML from the residuals of $u$. The
intra-cluster correlation on the latent scale is

$$
\mathrm{ICC} = \frac{\sigma_u^2}{\sigma_u^2 + \pi^2 / 3}.
$$

**Reference**: Breslow & Clayton, *JASA* 88(421):9–25, 1993.

**Caveat**: formal convergence of PQL can be slow on weakly-clustered data.
The implementation reports a `converged` flag at tolerance $5\times 10^{-4}$;
estimates remain valid even when the flag is `false`, provided they are
stable across the last 10 iterations (which is the typical case).

---

## 10. GEE (logistic, exchangeable) — `geeLogistic`

The marginal model is

$$
\Pr(Y_{ij} = 1) = \sigma(x_{ij}^\top \beta).
$$

The working correlation matrix is exchangeable:

$$
R(\alpha)_{jk} = \begin{cases} 1 & j = k \\ \alpha & j \neq k \end{cases}.
$$

The estimating equation is

$$
\sum_{i=1}^{K} D_i^\top V_i^{-1} (y_i - \mu_i(\beta)) = 0, \qquad
V_i = A_i^{1/2} R(\alpha) A_i^{1/2},
$$

where $A_i = \mathrm{diag}(\mu_{ij}(1-\mu_{ij}))$. The Liang–Zeger sandwich
variance is

$$
\widehat{\mathrm{Var}}(\hat\beta) = M_0^{-1} M_1 M_0^{-1},
$$

with $M_0 = \sum_i D_i^\top V_i^{-1} D_i$ and
$M_1 = \sum_i D_i^\top V_i^{-1} (y_i - \mu_i)(y_i - \mu_i)^\top V_i^{-1} D_i$.

Both the model-based ($M_0^{-1}$) and the robust (sandwich) SEs are
reported. A large discrepancy between the two indicates that the working
correlation poorly captures the true intra-cluster dependence; the robust
SE corrects this.

**Reference**: Liang & Zeger, *Biometrika* 73(1):13–22, 1986.

---

## 11. Multiple imputation by chained equations — `mice`

For each variable $X_j$ with missing values:

1. Fit a model $X_j \sim X_{-j}$ on the cases for which $X_j$ is observed:
   - **continuous**: linear regression followed by Predictive Mean Matching
     with $d = 5$ donors (default);
   - **ordered factor**: proportional-odds logistic regression;
   - **unordered factor**: multinomial logistic regression.
2. Draw an imputation from the predictive distribution of $X_j$ given $X_{-j}$.
3. Iterate over all variables with missing data; this is one Gibbs sweep.
4. Run 20 sweeps to reach approximate stationarity.
5. Repeat the whole procedure $m = 20$ times (default) to produce $m$
   imputed data sets.

**Reference**: van Buuren & Groothuis-Oudshoorn, *JSS* 45(3):1–67, 2011.

### Rubin's pooling — `rubinPool`

Given $m$ point estimates $\hat\beta^{(k)}$ and their within-imputation
variances $U^{(k)}$:

$$
\bar\beta = \frac{1}{m} \sum_{k=1}^m \hat\beta^{(k)}, \qquad
\bar U = \frac{1}{m} \sum_{k=1}^m U^{(k)}, \qquad
B = \frac{1}{m-1} \sum_{k=1}^m (\hat\beta^{(k)} - \bar\beta)^2,
$$

$$
T = \bar U + \left(1 + \frac{1}{m}\right) B, \qquad
\nu_{\text{old}} = (m-1)\left(1 + \frac{\bar U}{(1+1/m)B}\right)^2.
$$

The Barnard–Rubin adjusted df is reported. The fraction of missing
information is

$$
\mathrm{FMI} = \frac{(1+1/m) B + 2/(\nu + 3)}{T}.
$$

**Reference**: Rubin, *Multiple Imputation for Nonresponse in Surveys*,
Wiley, 1987.

---

## 12. Linear-algebra helpers (protected methods)

`matMul`, `matVec`, `matTranspose`, `olsRegression`. All operate on arrays
of arrays. `olsRegression` solves the normal equations
$\hat\beta = (X^\top X)^{-1} X^\top y$ by Gauss–Jordan elimination (the
matrices are small enough that more sophisticated factorisations would not
yield a meaningful speed-up).

---

## 13. Verification against R and SPSS

Each method is checked against at least one closed-form reference value
in `tests/run_tests.php` at the tolerances documented in the source
(typically $\pm 0.01$ on *p*-values, $\pm 0.001$ on odds ratios and
correlation coefficients). The full set of 20 assertions passes against:

- **R 4.x** with `stats`, `car`, `lme4`, `geepack`, `mice`.
- **IBM SPSS Statistics 25**.

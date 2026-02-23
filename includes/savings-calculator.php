<?php
/**
 * Savings Calculator Section
 * Include this file wherever you need the calculator:
 * <?php include "includes/savings-calculator.php"; ?>
 */
?>

<!-- Savings Calculator -->
<section class="savings-calculator">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="calculator-box collapsed" id="calculatorBox" data-aos="fade-up">
                    <div class="savings-icon">
                        <i class="fa-regular fa-lightbulb"></i>
                    </div>
                    <h2>Let's check how much you can save!</h2>
                    <p>What's your monthly electric bill?</p>
                    <div class="row justify-content-center mb-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group-custom">
                                <input
                                    type="number"
                                    id="billAmount"
                                    placeholder="0"
                                    min="0"
                                    step="0.01"
                                    onfocus="expandCalculator()"
                                    onblur="shrinkCalculatorIfEmpty()">
                                <p>Monthly Electric Bill (₱)</p>
                            </div>
                        </div>
                    </div>
                    <button class="calculate-btn" onclick="calculateSavings()">Calculate</button>
                    <div id="errorMessage" class="error-message"></div>
                    <div id="results" class="results">
                        <div class="result-card">
                            <div class="result-value" id="kwpValue">0.0</div>
                            <div class="result-label">Required System Size (kWp)</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="panelsValue">0</div>
                            <div class="result-label">Solar Panels</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="monthlySavings">0</div>
                            <div class="result-label">Monthly Savings (₱)</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value" id="yearlySavings">0</div>
                            <div class="result-label">Yearly Savings (₱)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ============================================
// SAVINGS CALCULATOR JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    setupCalculator();

    const calculatorBox = document.getElementById('calculatorBox');
    if (calculatorBox) {
        calculatorBox.classList.add('collapsed');
    }

    // Bulb icon click handler
    const bulbIcon = document.querySelector('.savings-icon');
    if (bulbIcon) {
        bulbIcon.addEventListener('click', function () {
            this.style.animation = 'none';
            setTimeout(() => { this.style.animation = ''; }, 10);

            const billInput = document.getElementById('billAmount');
            if (calculatorBox && calculatorBox.classList.contains('collapsed')) {
                expandCalculator();
                if (billInput) {
                    setTimeout(() => billInput.focus(), 300);
                }
            }
        });
    }
});

function setupCalculator() {
    const billInput = document.getElementById('billAmount');
    if (billInput) {
        billInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                calculateSavings();
            }
        });
    }
}

function expandCalculator() {
    const calculatorBox = document.getElementById('calculatorBox');
    const bulbIcon = document.querySelector('.savings-icon');

    if (calculatorBox) {
        calculatorBox.classList.remove('collapsed');
        calculatorBox.classList.add('expanded');
    }
    if (bulbIcon) {
        bulbIcon.classList.add('active');
    }
}

function shrinkCalculatorIfEmpty() {
    const billInput = document.getElementById('billAmount');
    const calculatorBox = document.getElementById('calculatorBox');
    const results = document.getElementById('results');
    const bulbIcon = document.querySelector('.savings-icon');

    if (calculatorBox && billInput && !billInput.value && !results.classList.contains('show')) {
        setTimeout(() => {
            calculatorBox.classList.remove('expanded');
            calculatorBox.classList.add('collapsed');
            if (bulbIcon) {
                bulbIcon.classList.remove('active');
            }
        }, 200);
    }
}

function calculateSavings() {
    const billAmount = parseFloat(document.getElementById('billAmount').value);
    const errorMessage = document.getElementById('errorMessage');
    const results = document.getElementById('results');
    const calculatorBox = document.getElementById('calculatorBox');

    if (!billAmount || billAmount <= 0) {
        errorMessage.textContent = 'Please enter a valid electric bill amount';
        results.classList.remove('show');
        return;
    }

    errorMessage.textContent = '';

    if (calculatorBox) {
        calculatorBox.classList.remove('collapsed');
        calculatorBox.classList.add('expanded');
    }

    const avgRate       = 13.40;
    const sunHours      = 4.5;
    const efficiency    = 0.85;
    const panelWattage  = 705;
    const savingsPct    = 0.95;

    const monthlyKwh   = billAmount / avgRate;
    const dailyKwh     = monthlyKwh / 30;
    const requiredKwp  = dailyKwh / (sunHours * efficiency);
    const panels       = Math.ceil((requiredKwp * 1000) / panelWattage);
    const monthly      = billAmount * savingsPct;
    const yearly       = monthly * 12;

    setTimeout(() => {
        document.getElementById('kwpValue').textContent      = requiredKwp.toFixed(1);
        document.getElementById('panelsValue').textContent   = panels;
        document.getElementById('monthlySavings').textContent = '₱' + monthly.toLocaleString('en-PH', { maximumFractionDigits: 0 });
        document.getElementById('yearlySavings').textContent  = '₱' + yearly.toLocaleString('en-PH',  { maximumFractionDigits: 0 });
        results.classList.add('show');
    }, 100);
}
</script>
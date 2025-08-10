*/

// --- public/assets/js/main.js --- (COMPLETO Y ACTUALIZADO PARA V9)
/*
document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'api/';
    // Auth Elements
    const authNav = document.getElementById('auth-nav');
    const authSection = document.getElementById('auth-section');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegisterLink = document.getElementById('show-register-link');
    const showLoginLink = document.getElementById('show-login-link');
    const authMessage = document.getElementById('auth-message');

    // Calculator Elements
    const calculatorSection = document.getElementById('calculator-section');
    const calculatorForm = document.getElementById('tax-calculator-form');
    const productNameInput = document.getElementById('productName');
    const tariffCodeSearchInput = document.getElementById('tariff-code-search');
    const tariffCodeIdSelect = document.getElementById('tariffCodeId');
    const tariffDetailsPreview = document.getElementById('tariff-details-preview');
    const cantidadInput = document.getElementById('cantidad');
    const valorFOBInput = document.getElementById('valorFOB');
    const pesoUnitarioKgInput = document.getElementById('pesoUnitarioKg');
    const esCourier4x4Checkbox = document.getElementById('esCourier4x4');
    const profitPercentageInput = document.getElementById('profitPercentage');
    
    // NEW Calculator Elements for Proration
    const totalFleteInput = document.getElementById('totalFlete');
    const totalSeguroInput = document.getElementById('totalSeguro');
    const totalAgenteAduanaInput = document.getElementById('totalAgenteAduana');
    const totalAlmacenajeInput = document.getElementById('totalAlmacenaje');
    const totalFleteInternoInput = document.getElementById('totalFleteInterno');
    const totalDemorajeInput = document.getElementById('totalDemoraje');
    const totalOtrosGastosInput = document.getElementById('totalOtrosGastos');
    const totalISDTasaInput = document.getElementById('totalISDTasa');
    const totalFOBEmbarqueInput = document.getElementById('totalFOBEmbarque');
    const totalPesoEmbarqueInput = document.getElementById('totalPesoEmbarque');
    const prorationMethodSelect = document.getElementById('prorationMethod');

    const calculationResultsDiv = document.getElementById('calculation-results');
    const resItemDetailsDiv = document.getElementById('res-item-details');
    const resCifSpan = document.getElementById('res-cif');
    const resBaseIvaSpan = document.getElementById('res-base-iva');
    const resAdValoremSpan = document.getElementById('res-advalorem');
    const resFodinfaSpan = document.getElementById('res-fodinfa');
    const resIceSpan = document.getElementById('res-ice');
    const resSpecificTaxSpan = document.getElementById('res-specifictax');
    const resIvaSpan = document.getElementById('res-iva');
    const resTotalImpuestosSpan = document.getElementById('res-total-impuestos');
    const resIsdTasaItemSpan = document.getElementById('res-isd-tasa-item');
    const resIsdItemSpan = document.getElementById('res-isd-item');
    const resAgenteAduanaItemSpan = document.getElementById('res-agente-aduana-item');
    const resOtrosGastosItemSpan = document.getElementById('res-otros-gastos-item');
    const resCostoTotalLineaSpan = document.getElementById('res-costo-total-linea');
    const resCostUnitFinalSpan = document.getElementById('res-cost-unit-final');
    const resProfitPercentageAppliedSpan = document.getElementById('res-profit-percentage-applied');
    const resProfitAmountUnitSpan = document.getElementById('res-profit-amount-unit');
    const resPvpUnitSpan = document.getElementById('res-pvp-unit');
    const resPvpTotalLineSpan = document.getElementById('res-pvp-total-line');
    const calculatorMessage = document.getElementById('calculator-message');
    const saveCalculationButton = document.getElementById('save-calculation-button');
    const printItemSummaryButton = document.getElementById('print-item-summary-button');
    const editingCalculationIdInput = document.getElementById('editing-calculation-id');

    // CSV Import Elements
    const csvImportSection = document.getElementById('csv-import-section');
    const csvImportForm = document.getElementById('csv-import-form');
    const csvFileInput = document.getElementById('csvFile');
    const fleteGeneralCsvInput = document.getElementById('fleteGeneralCsv');
    const seguroGeneralCsvInput = document.getElementById('seguroGeneralCsv');
    const gastosAgenteAduanaCsvInput = document.getElementById('gastosAgenteAduanaCsv');
    const tasaIsdConfigurableCsvInput = document.getElementById('tasaIsdConfigurableCsv');
    const gastosBodegaAduanaCsvInput = document.getElementById('gastosBodegaAduanaCsv');
    const gastosDemorajeCsvInput = document.getElementById('gastosDemorajeCsv');
    const gastosFleteTerrestreCsvInput = document.getElementById('gastosFleteTerrestreCsv');
    const gastosVariosCsvInput = document.getElementById('gastosVariosCsv');
    const prorationMethodCsvSelect = document.getElementById('prorationMethodCsv');
    const profitPercentageCsvInput = document.getElementById('profitPercentageCsv');
    const csvResultsSummaryDiv = document.getElementById('csv-results-summary');
    const csvResultsDetailsDiv = document.getElementById('csv-results-details');
    const printCsvSummaryButton = document.getElementById('print-csv-summary-button');
    const csvMessage = document.getElementById('csv-message');

    // Saved Calculations Elements
    const savedCalculationsSection = document.getElementById('saved-calculations-section');
    const savedCalculationsListDiv = document.getElementById('saved-calculations-list');
    const savedCalculationsMessage = document.getElementById('saved-calculations-message');
    
    // Tariff Management Elements
    const tariffManagementSection = document.getElementById('tariff-management-section');
    const tariffCodeForm = document.getElementById('tariff-code-form');
    const tariffEditIdInput = document.getElementById('tariff-edit-id');
    const tariffCodeValInput = document.getElementById('tariff-code-val');
    const tariffDescriptionValInput = document.getElementById('tariff-description-val');
    const tariffAdvaloremValInput = document.getElementById('tariff-advalorem-val');
    const tariffIceValInput = document.getElementById('tariff-ice-val');
    const tariffIvaValInput = document.getElementById('tariff-iva-val');
    const tariffSpecificValueValInput = document.getElementById('tariff-specific-value-val');
    const tariffSpecificUnitValInput = document.getElementById('tariff-specific-unit-val');
    const tariffFodinfaAppliesCheckbox = document.getElementById('tariff-fodinfa-applies');
    const tariffNotesValTextarea = document.getElementById('tariff-notes-val');
    const tariffFormSubmitButton = document.getElementById('tariff-form-submit-button');
    const tariffFormCancelButton = document.getElementById('tariff-form-cancel-button');
    const tariffListDiv = document.getElementById('tariff-list');
    const tariffMessage = document.getElementById('tariff-message');

    // CSV History Elements
    const csvHistorySection = document.getElementById('csv-history-section');
    const csvHistoryListDiv = document.getElementById('csv-history-list');
    const csvHistoryMessage = document.getElementById('csv-history-message');

    const printableAreaDiv = document.getElementById('printable-area');

    let currentUser = null;
    let currentCalculatedResults = null; 
    let lastCsvProcessedData = null;    
    let debounceTimer;

    function displayMessage(element, message, isSuccess) {
        if (!element) return;
        element.textContent = message;
        element.className = 'message ' + (isSuccess ? 'success' : 'error');
        element.style.display = 'block';
        setTimeout(() => { if(element) {element.style.display = 'none'; element.textContent = '';} }, 7000);
    }

    function updateAuthUI() {
        const isAdmin = currentUser && currentUser.email === 'admin@example.com'; // Ejemplo de rol admin. Cambiar por un sistema de roles real.
        if (currentUser) {
            authNav.innerHTML = `<span>Hola, ${currentUser.email}</span> <button id="logout-button">Logout</button>`;
            authSection.style.display = 'none';
            calculatorSection.style.display = 'block';
            saveCalculationButton.style.display = 'inline-block';
            savedCalculationsSection.style.display = 'block';
            csvImportSection.style.display = 'block'; 
            tariffManagementSection.style.display = isAdmin ? 'block' : 'none';
            csvHistorySection.style.display = 'block';
            loadSavedCalculations();
            if(isAdmin) loadTariffCodesForManagement();
            loadCsvImportHistory();
            document.getElementById('logout-button').addEventListener('click', handleLogout);
        } else {
            authNav.innerHTML = `<button id="show-login-form-button">Login</button> <button id="show-register-form-button">Registro</button>`;
            authSection.style.display = 'block';
            calculatorSection.style.display = 'none';
            loginForm.style.display = 'block'; registerForm.style.display = 'none';
            saveCalculationButton.style.display = 'none'; printItemSummaryButton.style.display = 'none';
            savedCalculationsSection.style.display = 'none'; csvImportSection.style.display = 'none';
            tariffManagementSection.style.display = 'none'; csvHistorySection.style.display = 'none';
            document.getElementById('show-login-form-button')?.addEventListener('click', () => { loginForm.style.display = 'block'; registerForm.style.display = 'none'; authMessage.style.display = 'none'; });
            document.getElementById('show-register-form-button')?.addEventListener('click', () => { loginForm.style.display = 'none'; registerForm.style.display = 'block'; authMessage.style.display = 'none'; });
        }
    }

    showRegisterLink?.addEventListener('click', (e) => { e.preventDefault(); loginForm.style.display = 'none'; registerForm.style.display = 'block'; authMessage.style.display = 'none'; });
    showLoginLink?.addEventListener('click', (e) => { e.preventDefault(); registerForm.style.display = 'none'; loginForm.style.display = 'block'; authMessage.style.display = 'none'; });

    loginForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        formData.append('action', 'login');
        try {
            const response = await fetch(`${API_BASE_URL}auth.php`, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                currentUser = data.user;
                updateAuthUI();
                displayMessage(authMessage, 'Login exitoso.', true);
            } else { displayMessage(authMessage, data.message || 'Error en login.', false); }
        } catch (error) { displayMessage(authMessage, 'Error de conexión.', false); }
    });

    registerForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        formData.append('action', 'register');
        try {
            const response = await fetch(`${API_BASE_URL}auth.php`, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                displayMessage(authMessage, 'Registro exitoso. Por favor, inicia sesión.', true);
                registerForm.reset();
                loginForm.style.display = 'block'; registerForm.style.display = 'none';
            } else { displayMessage(authMessage, data.message || 'Error en registro.', false); }
        } catch (error) { displayMessage(authMessage, 'Error de conexión.', false); }
    });

    async function handleLogout() {
        try {
            const response = await fetch(`${API_BASE_URL}auth.php?action=logout`);
            await response.json(); 
            currentUser = null;
            updateAuthUI();
            currentCalculatedResults = null;
            lastCsvProcessedData = null;
            calculatorForm?.reset();
            csvImportForm?.reset();
            if(calculationResultsDiv) calculationResultsDiv.style.display = 'none';
            if(printItemSummaryButton) printItemSummaryButton.style.display = 'none';
            if(csvResultsDetailsDiv) csvResultsDetailsDiv.innerHTML = '';
            if(csvResultsSummaryDiv) csvResultsSummaryDiv.innerHTML = '';
            if(printCsvSummaryButton) printCsvSummaryButton.style.display = 'none';
            if(savedCalculationsListDiv) savedCalculationsListDiv.innerHTML = '<p>Inicie sesión para ver sus cálculos.</p>';
            if(tariffListDiv) tariffListDiv.innerHTML = '';
            if(csvHistoryListDiv) csvHistoryListDiv.innerHTML = '';

        } catch (error) { console.error('Error en logout:', error); }
    }

    async function checkLoginStatus() {
        try {
            const response = await fetch(`${API_BASE_URL}auth.php?action=status`);
            const data = await response.json();
            if (data.success && data.loggedIn) { currentUser = data.user; } 
            else { currentUser = null; }
        } catch (error) { currentUser = null; }
        updateAuthUI();
    }

    tariffCodeSearchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const searchTerm = tariffCodeSearchInput.value.trim();
            if (searchTerm.length < 2 && searchTerm.length !==0) { 
                 tariffCodeIdSelect.innerHTML = '<option value="">Ingrese al menos 2 caracteres o seleccione</option>';
                 tariffDetailsPreview.textContent = ''; return;
            }
            if(searchTerm.length === 0){ 
                tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>';
                tariffDetailsPreview.textContent = ''; loadDefaultTariffCodes(); return;
            }
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read&term=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>';
                if (data.success && data.tariff_codes.length > 0) {
                    data.tariff_codes.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = `${code.code} - ${code.description}`;
                        tariffCodeIdSelect.appendChild(option);
                    });
                } else { tariffCodeIdSelect.innerHTML = '<option value="">No se encontraron partidas</option>'; }
            } catch (error) { tariffCodeIdSelect.innerHTML = '<option value="">Error al buscar</option>';}
            tariffDetailsPreview.textContent = ''; 
        }, 500); 
    });
    
    async function loadDefaultTariffCodes() { 
        if(!tariffCodeIdSelect) return;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read`);
            const data = await response.json();
            tariffCodeIdSelect.innerHTML = '<option value="">Seleccione una partida</option>'; 
            if (data.success && data.tariff_codes) {
                 data.tariff_codes.forEach(code => {
                    const option = document.createElement('option');
                    option.value = code.id;
                    option.textContent = `${code.code} - ${code.description}`;
                    tariffCodeIdSelect.appendChild(option);
                });
            }
        } catch (error) { console.error("Error cargando partidas por defecto:", error); }
    }

    tariffCodeIdSelect?.addEventListener('change', async () => {
        const selectedId = tariffCodeIdSelect.value;
        tariffDetailsPreview.textContent = ''; 
        if (selectedId) {
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=get_one&id=${selectedId}`);
                const data = await response.json();
                if (data.success && data.tariff_code) {
                    const tc = data.tariff_code;
                    tariffDetailsPreview.innerHTML = `
                        AdV: ${(parseFloat(tc.advalorem_rate) * 100).toFixed(2)}%, 
                        ICE: ${(tc.ice_rate ? parseFloat(tc.ice_rate) * 100 : 0).toFixed(2)}%, 
                        IVA: ${(parseFloat(tc.iva_rate) * 100).toFixed(2)}%
                        ${tc.specific_tax_value && parseFloat(tc.specific_tax_value) > 0 ? `, Espec: ${tc.specific_tax_value} ${tc.specific_tax_unit || ''}` : ''}
                    `;
                }
            } catch (error) { console.error("Error obteniendo detalle de partida:", error); }
        }
    });

    calculatorForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        // --- 1. Get Item-specific data ---
        const item = {
            tariffCodeId: tariffCodeIdSelect.value,
            cantidad: parseInt(cantidadInput.value) || 1,
            valorFOB: parseFloat(valorFOBInput.value) || 0,
            pesoUnitarioKg: parseFloat(pesoUnitarioKgInput.value) || 0
        };

        // --- 2. Get Total Shipment data ---
        const shipment = {
            totalFlete: parseFloat(totalFleteInput.value) || 0,
            totalSeguro: parseFloat(totalSeguroInput.value) || 0,
            totalAgenteAduana: parseFloat(totalAgenteAduanaInput.value) || 0,
            totalAlmacenaje: parseFloat(totalAlmacenajeInput.value) || 0,
            totalFleteInterno: parseFloat(totalFleteInternoInput.value) || 0,
            totalDemoraje: parseFloat(totalDemorajeInput.value) || 0,
            totalOtrosGastos: parseFloat(totalOtrosGastosInput.value) || 0,
            totalISDTasa: parseFloat(totalISDTasaInput.value) || 0,
            totalFOB: parseFloat(totalFOBEmbarqueInput.value) || 0,
            totalPeso: parseFloat(totalPesoEmbarqueInput.value) || 0,
            prorationMethod: prorationMethodSelect.value
        };

        // --- 3. Basic Validation ---
        if (!item.tariffCodeId) { displayMessage(calculatorMessage, 'Por favor, seleccione una partida arancelaria.', false); return; }
        if (item.cantidad <= 0) { displayMessage(calculatorMessage, 'La cantidad debe ser mayor a cero.', false); return; }
        if (shipment.prorationMethod === 'fob' && shipment.totalFOB <= 0) {
             displayMessage(calculatorMessage, 'El FOB Total del Embarque debe ser mayor a cero para prorratear por valor.', false); return;
        }
        if (shipment.prorationMethod === 'weight' && shipment.totalPeso <= 0) {
             displayMessage(calculatorMessage, 'El Peso Total del Embarque debe ser mayor a cero para prorratear por peso.', false); return;
        }

        // --- 4. Calculate Proration Factor ---
        let prorationFactor = 0;
        const itemFOBTotal = item.valorFOB * item.cantidad;
        const itemPesoTotal = item.pesoUnitarioKg * item.cantidad;

        if (shipment.prorationMethod === 'fob' && shipment.totalFOB > 0) {
            prorationFactor = itemFOBTotal / shipment.totalFOB;
        } else if (shipment.prorationMethod === 'weight' && shipment.totalPeso > 0) {
            prorationFactor = itemPesoTotal / shipment.totalPeso;
        }

        // --- 5. Calculate Prorated Costs for the Item ---
        const proratedFlete = shipment.totalFlete * prorationFactor;
        const proratedSeguro = shipment.totalSeguro * prorationFactor;
        const proratedAgenteAduana = shipment.totalAgenteAduana * prorationFactor;

        // Sum of other post-nationalization costs to be prorated
        const totalOtrosGastosSum = shipment.totalAlmacenaje + shipment.totalFleteInterno + shipment.totalDemoraje + shipment.totalOtrosGastos;
        const proratedOtrosGastos = totalOtrosGastosSum * prorationFactor;

        // --- 6. Prepare data for the API call ---
        const calculationData = {
            tariffCodeId: item.tariffCodeId,
            cantidad: item.cantidad,
            valorFOB: item.valorFOB,
            pesoUnitarioKg: item.pesoUnitarioKg,
            costoFlete: proratedFlete,
            costoSeguro: proratedSeguro,
            costoAgenteAduanaItem: proratedAgenteAduana,
            tasaIsdAplicableItem: shipment.totalISDTasa, // ISD Rate is for the whole shipment
            otrosGastosItem: proratedOtrosGastos,
            esCourier4x4: esCourier4x4Checkbox.checked,
            profitPercentage: parseFloat(profitPercentageInput.value) || 0
        };

        // --- 7. Call the API ---
        try {
            const response = await fetch(`${API_BASE_URL}calculate.php`, { 
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(calculationData)
            });
            const data = await response.json();

            if (data.success) {
                currentCalculatedResults = data; 
                const ci = data.calculoInput;
                const pInfo = ci.partidaArancelariaInfo;

                resItemDetailsDiv.innerHTML = `
                    <p>FOB Unitario: USD ${ci.valorFOBUnitario.toFixed(2)} x ${ci.cantidad} = <strong>FOB Total Ítem: USD ${ci.valorFOBTotalLinea.toFixed(2)}</strong></p>
                    <p>Peso Unitario: ${ci.pesoUnitarioKg.toFixed(3)} Kg x ${ci.cantidad} = <strong>Peso Total Ítem: ${ci.pesoTotalLineaKg.toFixed(3)} Kg</strong></p>
                    <p>Partida: ${pInfo.code} - ${pInfo.description}</p>
                    <p>Flete Ítem (Prorrateado): USD ${ci.costoFleteInternacionalItem.toFixed(2)} | Seguro Ítem (Prorrateado): USD ${ci.costoSeguroInternacionalItem.toFixed(2)}</p>
                    <p>Régimen 4x4 Aplicado (para este ítem): ${ci.isShipmentConsidered4x4 ? 'Sí' : 'No'}</p> 
                `;
                if(resCifSpan) resCifSpan.textContent = data.cif;
                if(resBaseIvaSpan) resBaseIvaSpan.textContent = data.baseImponibleIVA;
                
                if(resAdValoremSpan) resAdValoremSpan.textContent = data.adValorem;
                if(resFodinfaSpan) resFodinfaSpan.textContent = data.fodinfa;
                if(resIceSpan) resIceSpan.textContent = data.ice;
                if(resSpecificTaxSpan) resSpecificTaxSpan.textContent = data.specificTax;
                if(resIvaSpan) resIvaSpan.textContent = data.iva;
                if(resTotalImpuestosSpan) resTotalImpuestosSpan.textContent = data.totalImpuestos;

                if(resAgenteAduanaItemSpan) resAgenteAduanaItemSpan.textContent = ci.costoAgenteAduanaItem.toFixed(2); 
                if(resIsdTasaItemSpan) resIsdTasaItemSpan.textContent = ci.tasaISDAplicableAlFOB.toFixed(2);
                if(resIsdItemSpan) resIsdItemSpan.textContent = ci.isdPagadoItem.toFixed(2); 
                if(resOtrosGastosItemSpan) resOtrosGastosItemSpan.textContent = ci.otrosGastosPostNacionalizacionItem.toFixed(2);
                
                if(resCostoTotalLineaSpan) resCostoTotalLineaSpan.textContent = data.costoTotalEstimadoLinea;
                if(resCostUnitFinalSpan) resCostUnitFinalSpan.textContent = data.cost_price_unit_after_import;
                if(resProfitPercentageAppliedSpan) resProfitPercentageAppliedSpan.textContent = ci.profitPercentageApplied.toFixed(2);
                if(resProfitAmountUnitSpan) resProfitAmountUnitSpan.textContent = data.profit_amount_unit;
                if(resPvpUnitSpan) resPvpUnitSpan.textContent = data.pvp_unit;
                if(resPvpTotalLineSpan) resPvpTotalLineSpan.textContent = data.pvp_total_line;

                if(calculationResultsDiv) calculationResultsDiv.style.display = 'block';
                if(printItemSummaryButton) printItemSummaryButton.style.display = 'inline-block';
                if(calculatorMessage) calculatorMessage.style.display = 'none';
            } else {
                displayMessage(calculatorMessage, data.message || 'Error calculando impuestos.', false);
                if(calculationResultsDiv) calculationResultsDiv.style.display = 'none';
                if(printItemSummaryButton) printItemSummaryButton.style.display = 'none';
            }
        } catch (error) { 
            displayMessage(calculatorMessage, 'Error de conexión al calcular.', false);
            if(calculationResultsDiv) calculationResultsDiv.style.display = 'none';
            if(printItemSummaryButton) printItemSummaryButton.style.display = 'none';
         }
    });

    saveCalculationButton?.addEventListener('click', async () => {
        if (!currentUser) { displayMessage(calculatorMessage, 'Debe iniciar sesión para guardar.', false); return; }
        if (!currentCalculatedResults || !currentCalculatedResults.success) { displayMessage(calculatorMessage, 'Primero realiza un cálculo exitoso.', false); return; }
        const productName = productNameInput.value.trim();
        if (!productName) { displayMessage(calculatorMessage, 'Ingresa un nombre para el producto/cálculo.', false); return; }

        const ci = currentCalculatedResults.calculoInput;
        const dataToSave = {
            productName: productName,
            tariffCodeId: ci.partidaArancelariaInfo.id,
            valorFOBUnitario: ci.valorFOBUnitario,
            cantidad: ci.cantidad,
            pesoUnitarioKg: ci.pesoUnitarioKg,
            costoFleteInternacionalItem: ci.costoFleteInternacionalItem, 
            costoSeguroInternacionalItem: ci.costoSeguroInternacionalItem,
            costoAgenteAduanaItem: ci.costoAgenteAduanaItem, 
            isdPagadoItem: ci.isdPagadoItem, 
            otrosGastosPostNacionalizacionItem: ci.otrosGastosPostNacionalizacionItem,
            isShipmentConsidered4x4: ci.isShipmentConsidered4x4,
            cif: currentCalculatedResults.cif,
            adValorem: currentCalculatedResults.adValorem,
            fodinfa: currentCalculatedResults.fodinfa,
            ice: currentCalculatedResults.ice,
            specificTax: currentCalculatedResults.specificTax,
            iva: currentCalculatedResults.iva,
            totalImpuestos: currentCalculatedResults.totalImpuestos,
            costoTotalEstimadoLinea: currentCalculatedResults.costoTotalEstimadoLinea,
            profit_percentage_applied: ci.profitPercentageApplied,
            cost_price_unit_after_import: currentCalculatedResults.cost_price_unit_after_import,
            profit_amount_unit: currentCalculatedResults.profit_amount_unit,
            pvp_unit: currentCalculatedResults.pvp_unit,
            pvp_total_line: currentCalculatedResults.pvp_total_line
        };
        
        const editingId = editingCalculationIdInput.value;
        const action = editingId ? 'update' : 'save';
        if (editingId) dataToSave.id = editingId;

        try {
            const response = await fetch(`${API_BASE_URL}calculations.php?action=${action}`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dataToSave)
            });
            const result = await response.json();
            if (result.success) {
                displayMessage(savedCalculationsMessage, result.message, true);
                loadSavedCalculations();
                calculatorForm.reset(); 
                productNameInput.value = ''; tariffCodeIdSelect.value = ''; tariffDetailsPreview.textContent = '';
                editingCalculationIdInput.value = ''; currentCalculatedResults = null;
                calculationResultsDiv.style.display = 'none'; printItemSummaryButton.style.display = 'none';
                saveCalculationButton.textContent = 'Guardar Cálculo';
            } else { displayMessage(savedCalculationsMessage, result.message || 'Error guardando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al guardar.', false); }
    });
    
    async function loadSavedCalculations(filterCsvImportId = null) {
        if (!currentUser) return;
        let url = `${API_BASE_URL}calculations.php?action=load`;
        if (filterCsvImportId) {
            url += `&csv_import_id=${filterCsvImportId}`;
        }
        try {
            const response = await fetch(url);
            const data = await response.json();
            if (data.success) { renderSavedCalculations(data.calculations, filterCsvImportId); } 
            else { displayMessage(savedCalculationsMessage, data.message || 'Error cargando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al cargar.', false); }
    }

    function renderSavedCalculations(calculations, filterCsvImportId = null) {
        savedCalculationsListDiv.innerHTML = '';
        if (calculations.length === 0) {
            savedCalculationsListDiv.innerHTML = filterCsvImportId ? 
                `<p>No hay cálculos asociados a esta importación CSV (ID: ${filterCsvImportId}).</p>` :
                '<p>No tienes cálculos guardados.</p>'; 
            return;
        }
        if (filterCsvImportId) {
             savedCalculationsListDiv.innerHTML = `<h3>Cálculos para Importación CSV ID: ${filterCsvImportId}</h3>`;
        }

        const ul = document.createElement('ul');
        calculations.forEach(calc => {
            const li = document.createElement('li');
            li.innerHTML = `
                <div class="calc-info">
                    <strong>${calc.product_name}</strong> (Partida: ${calc.tariff_code_val || 'N/A'})<br>
                    <small>Cant: ${calc.cantidad}, FOB U: $${parseFloat(calc.valor_fob_unitario).toFixed(2)} | Costo Total Línea: $${parseFloat(calc.costo_total_estimado_linea).toFixed(2)} | PVP U: $${parseFloat(calc.pvp_unit || 0).toFixed(2)}</small><br>
                    <small>Guardado: ${new Date(calc.created_at).toLocaleDateString()} ${calc.csv_import_line_number ? `(CSV Línea: ${calc.csv_import_line_number})` : ''}</small>
                </div>
                <div class="actions">
                    <!-- Editing old calculations is disabled due to the new proration form structure -->
                    <!-- <button class="edit-calc" data-id="${calc.id}">Editar</button> -->
                    <button class="delete-calc" data-id="${calc.id}">Eliminar</button>
                </div>
            `;
            ul.appendChild(li);
        });
        savedCalculationsListDiv.appendChild(ul);

        /* Editing old calculations is disabled due to new proration form structure.
        document.querySelectorAll('.edit-calc').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                const calcToEdit = calculations.find(c => c.id.toString() === id);
                if (calcToEdit) loadCalculationForEdit(calcToEdit);
            });
        });
        */
        document.querySelectorAll('.delete-calc').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                if (confirm('¿Estás seguro de eliminar este cálculo?')) { deleteCalculation(id); }
            });
        });
    }

    // function loadCalculationForEdit(calc) { ... } // This function is now disabled

    async function deleteCalculation(id) {
         try {
            const response = await fetch(`${API_BASE_URL}calculations.php?action=delete`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) { displayMessage(savedCalculationsMessage, 'Cálculo eliminado.', true); loadSavedCalculations(); } 
            else { displayMessage(savedCalculationsMessage, result.message || 'Error eliminando.', false); }
        } catch (error) { displayMessage(savedCalculationsMessage, 'Error de conexión al eliminar.', false); }
    }

    function generatePrintableHTMLForItem(calcData, productName) {
        if (!calcData || !calcData.success || !calcData.calculoInput || !calcData.calculoInput.partidaArancelariaInfo) {
            return '<p>Datos insuficientes para generar resumen.</p>';
        }
        const ci = calcData.calculoInput;
        const pInfo = ci.partidaArancelariaInfo;
        const cTime = new Date().toLocaleString('es-EC', { timeZone: 'America/Guayaquil' });
        const advRatePercent = (parseFloat(pInfo.advalorem_rate) * 100).toFixed(2);
        const ivaRateFromTariffPercent = (parseFloat(pInfo.iva_rate) * 100).toFixed(2);
        const iceRateFromTariffPercent = (pInfo.ice_rate ? parseFloat(pInfo.ice_rate) * 100 : 0).toFixed(2);

        return \`
            <div id="print-content-item">
                <h2>Resumen de Cálculo de Importación</h2>
                <p><strong>Producto/Descripción:</strong> \${productName || 'N/A'}</p>
                <p><strong>Partida Arancelaria:</strong> \${pInfo.code} - \${pInfo.description}</p>
                <p><strong>Fecha del Cálculo:</strong> \${cTime}</p><hr>
                <div class="section-title">Valores de Entrada:</div>
                <table>
                    <tr><td>Cantidad:</td><td class="text-right">\${ci.cantidad}</td></tr>
                    <tr><td>FOB Unitario (USD):</td><td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td></tr>
                    <tr><td>Peso Unitario (Kg):</td><td class="text-right">\${ci.pesoUnitarioKg.toFixed(3)}</td></tr>
                    <tr><td>Flete Internacional Ítem (USD):</td><td class="text-right">\${ci.costoFleteInternacionalItem.toFixed(2)}</td></tr>
                    <tr><td>Seguro Internacional Ítem (USD):</td><td class="text-right">\${ci.costoSeguroInternacionalItem.toFixed(2)}</td></tr>
                    <tr><td>Régimen 4x4 Aplicado:</td><td class="text-right">\${ci.isShipmentConsidered4x4 ? 'Sí' : 'No'}</td></tr>
                </table>
                <div class="section-title">Desglose de Costos e Impuestos (USD):</div>
                <table>
                    <tr><td>FOB Total Ítem:</td><td class="text-right">\${ci.valorFOBTotalLinea.toFixed(2)}</td></tr>
                    <tr><td>Peso Total Ítem (Kg):</td><td class="text-right">\${ci.pesoTotalLineaKg.toFixed(3)}</td></tr>
                    <tr><td><strong>Valor CIF:</strong></td><td class="text-right"><strong>\${calcData.cif}</strong></td></tr>
                    <tr><td>Base Imponible IVA:</td><td class="text-right">\${calcData.baseImponibleIVA}</td></tr>
                    <tr><td colspan="2"><hr style="border-style:dashed; margin: 2px 0;"></td></tr>
                    <tr><td>AdValorem (\${advRatePercent}% s/CIF):</td><td class="text-right">\${calcData.adValorem}</td></tr>
                    <tr><td>FODINFA (0.5% s/CIF):</td><td class="text-right">\${calcData.fodinfa}</td></tr>
                    <tr><td>ICE (\${iceRateFromTariffPercent}% s/Base):</td><td class="text-right">\${calcData.ice}</td></tr>
                    <tr><td>Imp. Específico:</td><td class="text-right">\${calcData.specificTax}</td></tr>
                    <tr><td>IVA (\${ivaRateFromTariffPercent}% s/Base IVA):</td><td class="text-right">\${calcData.iva}</td></tr>
                    <tr class="total-row"><td><strong>Total Impuestos (AdV,FOD,ICE,IVA,Esp):</strong></td><td class="text-right"><strong>\${calcData.totalImpuestos}</strong></td></tr>
                    <tr><td colspan="2"><hr style="border-style:dashed; margin: 2px 0;"></td></tr>
                    <tr><td>ISD Pagado (\${ci.tasaISDAplicableAlFOB.toFixed(2)}% s/FOB):</td><td class="text-right">\${ci.isdPagadoItem.toFixed(2)}</td></tr>
                    <tr><td>Gastos Agente Aduana:</td><td class="text-right">\${ci.costoAgenteAduanaItem.toFixed(2)}</td></tr>
                    <tr><td>Otros Gastos (Bodega, Flete Terr, etc.):</td><td class="text-right">\${ci.otrosGastosPostNacionalizacionItem.toFixed(2)}</td></tr>
                    <tr class="total-row"><td>Costo Total Línea (Post-Importación y Gastos):</td><td class="text-right">\${calcData.costoTotalEstimadoLinea}</td></tr>
                </table>
                <div class="section-title">Cálculo de Precio de Venta (USD):</div>
                <table>
                    <tr><td>Costo Unitario (Post-Importación y Gastos):</td><td class="text-right">\${calcData.cost_price_unit_after_import}</td></tr>
                    <tr><td>Ganancia Aplicada (\${ci.profitPercentageApplied.toFixed(2)}%):</td><td class="text-right">\${calcData.profit_amount_unit} (por unidad)</td></tr>
                    <tr class="total-row"><td><strong>PVP Unitario Estimado:</strong></td><td class="text-right"><strong>\${calcData.pvp_unit}</strong></td></tr>
                    <tr class="total-row"><td>PVP Total Línea Estimado:</td><td class="text-right">\${calcData.pvp_total_line}</td></tr>
                </table>
                <div class="footer-notes">
                    <p>Este es un cálculo estimado basado en las tasas y datos proporcionados. Verifique con fuentes oficiales de SENAE.</p>
                    <p>Tasas base de la partida (\${pInfo.code}): AdValorem \${advRatePercent}%, ICE \${iceRateFromTariffPercent}%, IVA \${ivaRateFromTariffPercent}%.</p>
                </div>
            </div>
        \`;
    }
    printItemSummaryButton?.addEventListener('click', () => {
        if (currentCalculatedResults && currentCalculatedResults.success) {
            const productNameForPrint = productNameInput.value.trim() || 'Producto sin nombre';
            const printableHTML = generatePrintableHTMLForItem(currentCalculatedResults, productNameForPrint);
            printableAreaDiv.innerHTML = printableHTML;
            printableAreaDiv.style.display = 'block'; 
            window.print();
            printableAreaDiv.style.display = 'none'; 
        } else { alert("Primero realiza un cálculo exitoso."); }
    });

    csvImportForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!csvFileInput.files.length) { displayMessage(csvMessage, 'Seleccione un archivo CSV.', false); return; }
        
        const formData = new FormData();
        formData.append('csvFile', csvFileInput.files[0]);
        formData.append('fleteGeneralCsv', fleteGeneralCsvInput.value || '0');
        formData.append('seguroGeneralCsv', seguroGeneralCsvInput.value || '0');
        formData.append('profitPercentageCsv', profitPercentageCsvInput.value || '0');
        formData.append('gastosAgenteAduanaCsv', gastosAgenteAduanaCsvInput.value || '0');
        formData.append('tasaIsdConfigurableCsv', tasaIsdConfigurableCsvInput.value || '0');
        formData.append('gastosBodegaAduanaCsv', gastosBodegaAduanaCsvInput.value || '0');
        formData.append('gastosDemorajeCsv', gastosDemorajeCsvInput.value || '0');
        formData.append('gastosFleteTerrestreCsv', gastosFleteTerrestreCsvInput.value || '0');
        formData.append('gastosVariosCsv', gastosVariosCsvInput.value || '0');
        formData.append('prorationMethodCsv', prorationMethodCsvSelect.value); 
        
        csvMessage.textContent = 'Procesando CSV...'; csvMessage.className = 'message'; csvMessage.style.display = 'block';
        csvResultsDetailsDiv.innerHTML = ''; csvResultsSummaryDiv.innerHTML = '';
        printCsvSummaryButton.style.display = 'none';

        try {
            const response = await fetch(`${API_BASE_URL}import_csv.php`, { method: 'POST', body: formData });
            const data = await response.json();
            lastCsvProcessedData = data; 

            if (data.success || (data.items_processed_details && data.items_processed_details.length > 0)) {
                displayMessage(csvMessage, data.message, data.success);
                if (data.items_processed_details && data.items_processed_details.length > 0) {
                    printCsvSummaryButton.style.display = 'inline-block';
                }
                
                if (data.consolidated_results) {
                    const s = data.consolidated_results;
                    csvResultsSummaryDiv.innerHTML = \`
                        <div class="section-title">Resumen Consolidado de Importación CSV (ID: \${data.csv_import_id || 'N/A'})</div>
                        <table>
                            <tr><td>Ítems CSV Procesados:</td><td class="text-right">\${s.total_items_csv}</td></tr>
                            <tr><td>Embarque Califica como 4x4:</td><td class="text-right">\${s.embarque_califica_4x4 ? 'Sí' : 'No'}</td></tr>
                            <tr><td>Método de Prorrateo Usado:</td><td class="text-right">\${s.proration_method_used === 'fob' ? 'Por Valor FOB' : (s.proration_method_used === 'weight' ? 'Por Peso' : 'N/A')}</td></tr>
                            <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                            <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                            <tr><td>Flete Internacional General (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td>Seguro Internacional General (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                            <tr><td>Gastos Agente Aduana Total (USD):</td><td class="text-right">\${s.gastos_agente_aduana_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Tasa ISD Aplicada al Embarque (%):</td><td class="text-right">\${s.tasa_isd_aplicada_embarque.toFixed(2)}</td></tr>
                            <tr><td>ISD Total Pagado (Embarque) (USD):</td><td class="text-right">\${s.isd_total_pagado_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Bodega Aduana Total (USD):</td><td class="text-right">\${s.gastos_bodega_aduana_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Demoraje Total (USD):</td><td class="text-right">\${s.gastos_demoraje_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Flete Terrestre Total (USD):</td><td class="text-right">\${s.gastos_flete_terrestre_total_embarque.toFixed(2)}</td></tr>
                            <tr><td>Gastos Varios Total (USD):</td><td class="text-right">\${s.gastos_varios_total_embarque.toFixed(2)}</td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                            <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td>Suma Agente Aduana Prorrateado (USD):</td><td class="text-right">\${(s.sum_agente_aduana_lineas || 0).toFixed(2)}</td></tr>
                            <tr><td>Suma ISD Pagado Prorrateado (USD):</td><td class="text-right">\${(s.sum_isd_pagado_lineas || 0).toFixed(2)}</td></tr>
                            <tr><td>Suma Otros Gastos Prorrateados (USD):</td><td class="text-right">\${(s.sum_otros_gastos_post_nacionalizacion_lineas || 0).toFixed(2)}</td></tr>
                            <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import y Gastos) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                            <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                        </table>
                    \`;
                }

                if (data.items_processed_details && data.items_processed_details.length > 0) {
                    let detailsHtml = '<div class="section-title">Detalle por Ítem del CSV</div><table><thead><tr>' +
                                      '<th>Línea</th><th>Desc.</th><th>Partida</th><th>Cant.</th><th>FOB U.</th>' +
                                      '<th>Flete Ítem</th><th>Seguro Ítem</th><th>Ag.Adu.Ítem</th><th>ISD Ítem</th><th>OtrosGast.Ítem</th>' +
                                      '<th>CIF Ítem</th><th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>' +
                                      '</tr></thead><tbody>';
                    data.items_processed_details.forEach(item => {
                        const calc = item.calculation;
                        if (calc.success) {
                            const ci = calc.calculoInput;
                            detailsHtml += \`<tr>
                                <td>\${item.line_csv_num}</td>
                                <td>\${item.description}</td>
                                <td>\${ci.partidaArancelariaInfo.code}</td>
                                <td class="text-right">\${ci.cantidad}</td>
                                <td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoFleteInternacionalItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoSeguroInternacionalItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.costoAgenteAduanaItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.isdPagadoItem.toFixed(2)}</td>
                                <td class="text-right">\${ci.otrosGastosPostNacionalizacionItem.toFixed(2)}</td>
                                <td class="text-right">\${calc.cif}</td>
                                <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                                <td class="text-right">\${calc.pvp_unit}</td>
                                <td class="text-right">\${calc.pvp_total_line}</td>
                            </tr>\`;
                        } else { 
                            detailsHtml += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="12" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
                        }
                    });
                    detailsHtml += '</tbody></table>';
                    csvResultsDetailsDiv.innerHTML = detailsHtml;
                }
                 if (data.errors_list && data.errors_list.length > 0) {
                    let errorHtml = '<div class="section-title" style="color:red;">Errores Durante el Procesamiento:</div><ul>';
                    data.errors_list.forEach(err => { errorHtml += \`<li style="color:red;">\${err}</li>\`; });
                    errorHtml += '</ul>';
                    csvResultsDetailsDiv.innerHTML = errorHtml + csvResultsDetailsDiv.innerHTML;
                }
                if (data.success) loadCsvImportHistory(); 

            } else {
                displayMessage(csvMessage, data.message || 'Error procesando CSV.', false);
                printCsvSummaryButton.style.display = 'none';
            }
        } catch (error) {
            displayMessage(csvMessage, 'Error de conexión al procesar CSV.', false);
            printCsvSummaryButton.style.display = 'none';
            console.error("Error CSV:", error);
        }
    });
    
    function generatePrintableHTMLForCSV(csvData) {
        if (!csvData || (!csvData.items_processed_details && !csvData.consolidated_results)) {
             return '<p>No hay datos del CSV para imprimir.</p>';
        }
        const cTime = new Date().toLocaleString('es-EC', { timeZone: 'America/Guayaquil' });
        let html = \`
            <div id="print-content-csv">
                <h2>Resumen de Importación Masiva (CSV)</h2>
                <p><strong>ID de Importación:</strong> \${csvData.csv_import_id || 'N/A'}</p>
                <p><strong>Fecha del Cálculo:</strong> \${cTime}</p>
                <hr>
        \`;

        if (csvData.consolidated_results) {
            const s = csvData.consolidated_results;
            html += \`
                <div class="section-title">Resumen Consolidado del Embarque</div>
                <table>
                    <tr><td>Ítems CSV Procesados:</td><td class="text-right">\${s.total_items_csv}</td></tr>
                    <tr><td>Embarque Califica como 4x4:</td><td class="text-right">\${s.embarque_califica_4x4 ? 'Sí' : 'No'}</td></tr>
                    <tr><td>Método de Prorrateo Usado:</td><td class="text-right">\${s.proration_method_used === 'fob' ? 'Por Valor FOB' : (s.proration_method_used === 'weight' ? 'Por Peso' : 'N/A')}</td></tr>
                    <tr><td>FOB Total Embarque (USD):</td><td class="text-right">\${s.gran_total_fob_embarque.toFixed(2)}</td></tr>
                    <tr><td>Peso Total Embarque (Kg):</td><td class="text-right">\${s.gran_total_peso_embarque_kg.toFixed(3)}</td></tr>
                    <tr><td>Flete Internacional General (USD):</td><td class="text-right">\${s.flete_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td>Seguro Internacional General (USD):</td><td class="text-right">\${s.seguro_general_aplicado_total.toFixed(2)}</td></tr>
                    <tr><td>Gastos Agente Aduana Total (USD):</td><td class="text-right">\${s.gastos_agente_aduana_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Tasa ISD Aplicada al Embarque (%):</td><td class="text-right">\${s.tasa_isd_aplicada_embarque.toFixed(2)}</td></tr>
                    <tr><td>ISD Total Pagado (Embarque) (USD):</td><td class="text-right">\${s.isd_total_pagado_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Bodega Aduana Total (USD):</td><td class="text-right">\${s.gastos_bodega_aduana_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Demoraje Total (USD):</td><td class="text-right">\${s.gastos_demoraje_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Flete Terrestre Total (USD):</td><td class="text-right">\${s.gastos_flete_terrestre_total_embarque.toFixed(2)}</td></tr>
                    <tr><td>Gastos Varios Total (USD):</td><td class="text-right">\${s.gastos_varios_total_embarque.toFixed(2)}</td></tr>
                    <tr><td colspan="2" style="border:none; padding:5px 0;"></td></tr>
                    <tr><td>Suma CIF de Líneas (USD):</td><td class="text-right">\${s.sum_cif_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma AdValorem (USD):</td><td class="text-right">\${s.sum_advalorem_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma FODINFA (USD):</td><td class="text-right">\${s.sum_fodinfa_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma ICE (USD):</td><td class="text-right">\${s.sum_ice_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma Imp. Específicos (USD):</td><td class="text-right">\${s.sum_specific_tax_lineas.toFixed(2)}</td></tr>
                    <tr><td>Suma IVA (USD):</td><td class="text-right">\${s.sum_iva_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL IMPUESTOS (USD):</strong></td><td class="text-right"><strong>\${s.sum_total_impuestos_lineas.toFixed(2)}</strong></td></tr>
                    <tr><td colspan="2"><hr style="border-style:dashed; margin: 2px 0;"></td></tr>
                    <tr><td>Suma Agente Aduana Prorrateado (USD):</td><td class="text-right">\${(s.sum_agente_aduana_lineas || 0).toFixed(2)}</td></tr>
                    <tr><td>Suma ISD Pagado Prorrateado (USD):</td><td class="text-right">\${(s.sum_isd_pagado_lineas || 0).toFixed(2)}</td></tr>
                    <tr><td>Suma Otros Gastos Prorrateados (USD):</td><td class="text-right">\${(s.sum_otros_gastos_post_nacionalizacion_lineas || 0).toFixed(2)}</td></tr>
                    <tr class="total-row"><td>GRAN TOTAL COSTO (Post-Import y Gastos) (USD):</td><td class="text-right">\${s.sum_costo_total_estimado_lineas.toFixed(2)}</td></tr>
                    <tr class="total-row"><td><strong>GRAN TOTAL PVP ESTIMADO (USD):</strong></td><td class="text-right"><strong>\${s.sum_pvp_total_lineas.toFixed(2)}</strong></td></tr>
                </table>
            \`;
        }

        if (csvData.items_processed_details && csvData.items_processed_details.length > 0) {
            html += \`<div class="section-title">Detalle por Ítem del CSV</div>
                     <table>
                        <thead>
                            <tr>
                                <th>Línea</th><th>Desc.</th><th>Partida</th><th>Cant.</th><th>FOB U.</th>
                                <th>Flete Ítem</th><th>Seguro Ítem</th><th>Ag.Adu.Ítem</th><th>ISD Ítem</th><th>OtrosGast.Ítem</th>
                                <th>CIF Ítem</th><th>Costo U. Post-Imp.</th><th>PVP U.</th><th>PVP Total Línea</th>
                            </tr>
                        </thead>
                        <tbody>\`;
            csvData.items_processed_details.forEach(item => {
                const calc = item.calculation;
                if (calc.success) {
                    const ci = calc.calculoInput;
                    html += \`<tr>
                        <td>\${item.line_csv_num}</td>
                        <td>\${item.description}</td>
                        <td>\${ci.partidaArancelariaInfo.code}</td>
                        <td class="text-right">\${ci.cantidad}</td>
                        <td class="text-right">\${ci.valorFOBUnitario.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoFleteInternacionalItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoSeguroInternacionalItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.costoAgenteAduanaItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.isdPagadoItem.toFixed(2)}</td>
                        <td class="text-right">\${ci.otrosGastosPostNacionalizacionItem.toFixed(2)}</td>
                        <td class="text-right">\${calc.cif}</td>
                        <td class="text-right">\${calc.cost_price_unit_after_import}</td>
                        <td class="text-right">\${calc.pvp_unit}</td>
                        <td class="text-right">\${calc.pvp_total_line}</td>
                    </tr>\`;
                } else {
                     html += \`<tr><td>\${item.line_csv_num}</td><td>\${item.description}</td><td colspan="12" style="color:red;">Error: \${calc.message || 'Desconocido'}</td></tr>\`;
                }
            });
            html += \`</tbody></table>\`;
        }
        
        if (csvData.errors_list && csvData.errors_list.length > 0) {
            html += \`<div class="section-title" style="color:red;">Errores Durante el Procesamiento:</div><ul>\`;
            csvData.errors_list.forEach(err => { html += \`<li style="color:red;">\${err}</li>\`; });
            html += \`</ul>\`;
        }
        html += '<div class="footer-notes"><p>Este es un cálculo estimado. Verifique con fuentes oficiales.</p></div></div>';
        return html;
    }

    printCsvSummaryButton?.addEventListener('click', () => {
        if (lastCsvProcessedData) {
            const printableHTML = generatePrintableHTMLForCSV(lastCsvProcessedData);
            printableAreaDiv.innerHTML = printableHTML;
            printableAreaDiv.style.display = 'block';
            window.print();
            printableAreaDiv.style.display = 'none';
        } else { alert("Primero procesa un archivo CSV exitosamente."); }
    });
    
    async function loadCsvImportHistory() {
        if (!currentUser || !csvHistoryListDiv) return;
        try {
            const response = await fetch(`${API_BASE_URL}csv_imports_history.php`);
            const data = await response.json();
            csvHistoryListDiv.innerHTML = ''; 
            if (data.success && data.history) {
                if (data.history.length === 0) {
                    csvHistoryListDiv.innerHTML = '<p>No hay importaciones CSV en el historial.</p>'; return;
                }
                const ul = document.createElement('ul');
                data.history.forEach(imp => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <div>
                            <strong>Archivo:</strong> ${imp.original_filename} <br>
                            <strong>Fecha:</strong> ${new Date(imp.upload_timestamp).toLocaleString()} <br>
                            <strong>Estado:</strong> ${imp.processing_status} | 
                            <strong>Líneas:</strong> ${imp.total_lines || 'N/A'} | 
                            <strong>Procesadas:</strong> ${imp.processed_lines || '0'} | 
                            <strong>Errores:</strong> ${imp.error_count || '0'}
                        </div>
                        <div class="actions">
                            <button class="view-csv-details-history" data-import-id="${imp.id}">Ver Cálculos</button>
                        </div>
                    `;
                    ul.appendChild(li);
                });
                csvHistoryListDiv.appendChild(ul);

                document.querySelectorAll('.view-csv-details-history').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const importId = e.target.dataset.id;
                        loadSavedCalculations(importId); 
                        savedCalculationsSection.scrollIntoView({ behavior: 'smooth' });
                    });
                });
            } else { displayMessage(csvHistoryMessage, data.message || 'Error cargando historial CSV.', false); }
        } catch (error) { displayMessage(csvHistoryMessage, 'Error de conexión al cargar historial CSV.', false); }
    }
    
    // --- Lógica de Gestión de Partidas Arancelarias (CRUD) ---
    tariffCodeForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tariffData = {
            id: tariffEditIdInput.value || null,
            code: tariffCodeValInput.value.trim(),
            description: tariffDescriptionValInput.value.trim(),
            advalorem_rate: parseFloat(tariffAdvaloremValInput.value),
            ice_rate: tariffIceValInput.value ? parseFloat(tariffIceValInput.value) : null,
            iva_rate: parseFloat(tariffIvaValInput.value),
            specific_tax_value: tariffSpecificValueValInput.value ? parseFloat(tariffSpecificValueValInput.value) : null,
            specific_tax_unit: tariffSpecificUnitValInput.value.trim() || null,
            fodinfa_applies: tariffFodinfaAppliesCheckbox.checked,
            notes: tariffNotesValTextarea.value.trim() || null
        };

        const action = tariffData.id ? 'update' : 'create';
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=${action}`, {
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(tariffData)
            });
            const result = await response.json();
            displayMessage(tariffMessage, result.message, result.success);
            if (result.success) {
                tariffCodeForm.reset();
                tariffEditIdInput.value = '';
                tariffFormSubmitButton.textContent = 'Guardar Partida';
                tariffFormCancelButton.style.display = 'none';
                loadTariffCodesForManagement(); 
                loadDefaultTariffCodes(); 
            }
        } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
    });

    tariffFormCancelButton?.addEventListener('click', () => {
        tariffCodeForm.reset();
        tariffEditIdInput.value = '';
        tariffFormSubmitButton.textContent = 'Guardar Partida';
        tariffFormCancelButton.style.display = 'none';
    });

    async function loadTariffCodesForManagement() {
        if(!tariffListDiv) return;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=read`); 
            const data = await response.json();
            tariffListDiv.innerHTML = '';
            if (data.success && data.tariff_codes.length > 0) {
                const table = document.createElement('table');
                table.innerHTML = `<thead><tr><th>Código</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody></tbody>`;
                const tbody = table.querySelector('tbody');
                data.tariff_codes.forEach(tc => {
                    const tr = tbody.insertRow();
                    tr.insertCell().textContent = tc.code;
                    tr.insertCell().textContent = tc.description;
                    const actionsCell = tr.insertCell();
                    const editBtn = document.createElement('button');
                    editBtn.textContent = 'Editar'; editBtn.className = 'edit-tariff'; editBtn.dataset.id = tc.id;
                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Eliminar'; deleteBtn.className = 'delete-tariff'; deleteBtn.dataset.id = tc.id;
                    actionsCell.appendChild(editBtn); actionsCell.appendChild(deleteBtn);
                });
                tariffListDiv.appendChild(table);

                document.querySelectorAll('.edit-tariff').forEach(btn => btn.addEventListener('click', handleEditTariff));
                document.querySelectorAll('.delete-tariff').forEach(btn => btn.addEventListener('click', handleDeleteTariff));

            } else { tariffListDiv.innerHTML = '<p>No hay partidas arancelarias definidas.</p>'; }
        } catch (error) { displayMessage(tariffMessage, 'Error cargando lista de partidas.', false); }
    }
    
    async function handleEditTariff(e) {
        const id = e.target.dataset.id;
        try {
            const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=get_one&id=${id}`);
            const data = await response.json();
            if (data.success && data.tariff_code) {
                const tc = data.tariff_code;
                tariffEditIdInput.value = tc.id;
                tariffCodeValInput.value = tc.code;
                tariffDescriptionValInput.value = tc.description;
                tariffAdvaloremValInput.value = tc.advalorem_rate;
                tariffIceValInput.value = tc.ice_rate || '';
                tariffIvaValInput.value = tc.iva_rate;
                tariffSpecificValueValInput.value = tc.specific_tax_value || '';
                tariffSpecificUnitValInput.value = tc.specific_tax_unit || '';
                tariffFodinfaAppliesCheckbox.checked = !!tc.fodinfa_applies;
                tariffNotesValTextarea.value = tc.notes || '';
                tariffFormSubmitButton.textContent = 'Actualizar Partida';
                tariffFormCancelButton.style.display = 'inline-block';
                tariffCodeValInput.focus();
            } else { displayMessage(tariffMessage, data.message || 'Error cargando partida para editar.', false); }
        } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
    }

    async function handleDeleteTariff(e) {
        const id = e.target.dataset.id;
        if (confirm(`¿Está seguro de eliminar la partida arancelaria con ID ${id}? Esta acción no se puede deshacer.`)) {
            try {
                const response = await fetch(`${API_BASE_URL}tariff_codes.php?action=delete`, {
                    method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id})
                });
                const result = await response.json();
                displayMessage(tariffMessage, result.message, result.success);
                if (result.success) { loadTariffCodesForManagement(); loadDefaultTariffCodes(); }
            } catch (error) { displayMessage(tariffMessage, 'Error de conexión.', false); }
        }
    }

    // --- Inicialización ---
    checkLoginStatus();
    loadDefaultTariffCodes(); 
});

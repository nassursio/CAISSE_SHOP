<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ajout de produit</title>
  <script src="https://cdn.jsdelivr.net/gh/dymosoftware/dymo-connect-framework/dymo.connect.framework.js">


</script>
  <style>
    :root {
      --bg: #f5f7fb;
      --card: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --border: #dbe3ee;
      --accent: #2563eb;
      --accent-hover: #1d4ed8;
      --danger: #b91c1c;
      --ok: #166534;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: var(--bg);
      color: var(--text);
      padding: 24px;
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 400px 1fr;
      gap: 20px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 22px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    h1 {
      margin: 0 0 8px 0;
      font-size: 28px;
    }

    h2 {
      margin: 0 0 12px 0;
      font-size: 22px;
    }

    .subtitle {
      margin: 0 0 20px 0;
      color: var(--muted);
      line-height: 1.5;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 14px;
    }

    label {
      font-weight: bold;
      font-size: 14px;
    }

    input, select {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--border);
      border-radius: 10px;
      font-size: 15px;
      font-family: inherit;
    }

    input:focus, select:focus {
      outline: 2px solid rgba(37, 99, 235, 0.15);
      border-color: var(--accent);
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 12px;
    }

    button {
      border: none;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .primary {
      background: var(--accent);
      color: white;
    }

    .primary:hover {
      background: var(--accent-hover);
    }

    .secondary {
      background: #e5e7eb;
      color: var(--text);
    }

    .secondary:hover {
      background: #d1d5db;
    }

    .status {
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 16px;
      font-size: 14px;
      line-height: 1.5;
      border: 1px solid var(--border);
      background: #f9fafb;
    }

    .status.ok {
      color: var(--ok);
      border-color: #bbf7d0;
      background: #f0fdf4;
    }

    .status.error {
      color: var(--danger);
      border-color: #fecaca;
      background: #fef2f2;
    }

    .preview-zone {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 360px;
      border: 2px dashed var(--border);
      border-radius: 14px;
      background: #fafcff;
      padding: 20px;
    }

    .preview-zone img {
      max-width: 100%;
      height: auto;
      border: 1px solid #d1d5db;
      background: white;
    }

    .muted {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.5;
    }

    code {
      background: #eef2ff;
      padding: 2px 6px;
      border-radius: 6px;
      font-size: 13px;
    }

    @media (max-width: 900px) {
      .container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Ajout de produit</h1>

      <div id="statusBox" class="status">Initialisation…</div>

      <div class="field">
        <label for="printerSelect">Imprimante DYMO</label>
        <select id="printerSelect"></select>
      </div>

      <div class="field">
        <label for="barcodeValue">BARCODE</label>
        <input id="barcodeValue" type="text" value="REF-1234" />
      </div>

      <div class="field">
        <label for="productName">NOM_PRODUIT</label>
        <input id="productName" type="text" value="10 KG de RIZ" />
      </div>

      <div class="field">
        <label for="priceValue">PRIX</label>
        <input id="priceValue" type="text" value="Prix : 9,99€" />
      </div>

      <div class="field">
        <label for="copies">Nombre d’exemplaires</label>
        <input id="copies" type="number" min="1" max="100" step="1" value="1" />
      </div>

    <div class="detail-image-zone">
      <img src="image_produit.png" alt="Image du produit" style="width:100%; max-width:300px; border:1px solid #E5E7EB; border-radius:10px; background:#F9FAFB; display:block; margin-top:10px;">
      <p style="margin-top:1rem; text-align:center; color:var(--muted); font-size:14px;">Aperçu du produit</p>
    </div>

      <div class="actions">
        <button class="secondary" id="addBtn" type="button">ajouter</button>
        <button class="secondary" id="previewBtn" type="button">Aperçu</button>
        <button class="primary" id="printBtn" type="button">Imprimer</button>
      </div>

    </div>

    <div class="card">
      <h2>Aperçu DYMO</h2>
      <div class="preview-zone" id="previewZone">
        <div class="muted">Aucun aperçu généré.</div>
      </div>
    </div>
  </div>

  <script>
    const statusBox = document.getElementById('statusBox');
    const printerSelect = document.getElementById('printerSelect');
    const barcodeValue = document.getElementById('barcodeValue');
    const productName = document.getElementById('productName');
    const priceValue = document.getElementById('priceValue');
    const copiesInput = document.getElementById('copies');
    const refreshBtn = document.getElementById('refreshBtn');
    const previewBtn = document.getElementById('previewBtn');
    const printBtn = document.getElementById('printBtn');
    const previewZone = document.getElementById('previewZone');

    const LABEL_XML = `<?xml version="1.0" encoding="utf-8"?>
<DieCutLabel Version="8.0" Units="twips" MediaType="Default">
	<PaperOrientation>Portrait</PaperOrientation>
	<Id>Small30334</Id>
	<IsOutlined>false</IsOutlined>
	<PaperName>30334 2-1/4 in x 1-1/4 in</PaperName>
	<DrawCommands>
		<RoundRectangle X="0" Y="0" Width="3240" Height="1800" Rx="270" Ry="270" />
	</DrawCommands>
	<ObjectInfo>
		<BarcodeObject>
			<Name>BARCODE</Name>
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />
			<LinkedObjectName />
			<Rotation>Rotation0</Rotation>
			<IsMirrored>False</IsMirrored>
			<IsVariable>True</IsVariable>
			<GroupID>-1</GroupID>
			<IsOutlined>False</IsOutlined>
			<Text>REF-1234</Text>
			<Type>Code128Auto</Type>
			<Size>Medium</Size>
			<TextPosition>Bottom</TextPosition>
			<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />
			<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />
			<TextEmbedding>None</TextEmbedding>
			<ECLevel>0</ECLevel>
			<HorizontalAlignment>Center</HorizontalAlignment>
			<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />
		</BarcodeObject>
		<Bounds X="228" Y="885" Width="2880" Height="720" />
	</ObjectInfo>
	<ObjectInfo>
		<TextObject>
			<Name>NOM_PRODUIT</Name>
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />
			<LinkedObjectName />
			<Rotation>Rotation0</Rotation>
			<IsMirrored>False</IsMirrored>
			<IsVariable>True</IsVariable>
			<GroupID>-1</GroupID>
			<IsOutlined>False</IsOutlined>
			<HorizontalAlignment>Center</HorizontalAlignment>
			<VerticalAlignment>Top</VerticalAlignment>
			<TextFitMode>ShrinkToFit</TextFitMode>
			<UseFullFontHeight>True</UseFullFontHeight>
			<Verticalized>False</Verticalized>
			<StyledText>
				<Element>
					<String xml:space="preserve">10 KG de RIZ</String>
					<Attributes>
						<Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />
					</Attributes>
				</Element>
			</StyledText>
		</TextObject>
		<Bounds X="888" Y="135" Width="1410" Height="255" />
	</ObjectInfo>
	<ObjectInfo>
		<TextObject>
			<Name>PRIX</Name>
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />
			<LinkedObjectName />
			<Rotation>Rotation0</Rotation>
			<IsMirrored>False</IsMirrored>
			<IsVariable>True</IsVariable>
			<GroupID>-1</GroupID>
			<IsOutlined>False</IsOutlined>
			<HorizontalAlignment>Center</HorizontalAlignment>
			<VerticalAlignment>Top</VerticalAlignment>
			<TextFitMode>ShrinkToFit</TextFitMode>
			<UseFullFontHeight>True</UseFullFontHeight>
			<Verticalized>False</Verticalized>
			<StyledText>
				<Element>
					<String xml:space="preserve">Prix : 9,99€</String>
					<Attributes>
						<Font Family="Arial" Size="12" Bold="False" Italic="False" Underline="False" Strikeout="False" />
						<ForeColor Alpha="255" Red="0" Green="0" Blue="0" HueScale="100" />
					</Attributes>
				</Element>
			</StyledText>
		</TextObject>
		<Bounds X="903" Y="480" Width="1560" Height="270" />
	</ObjectInfo>
</DieCutLabel>`;


// Validation du label DYMO
function validateLabel() {
    try {
        const label = dymo.label.framework.openLabelXml(LABEL_XML);

        const isValid = label.isValidLabel();
        const isDLS = label.isDLSLabel();
        const isDCD = label.isDCDLabel();

        const result = document.getElementById("validationResult");

        result.innerHTML = `
            Label valide : ${isValid}<br>
            Format DYMO Label Software (DLS) : ${isDLS}<br>
            Format DYMO Connect (DCD) : ${isDCD}
        `;

    } catch (e) {
        document.getElementById("validationResult").innerText = "Erreur de validation : " + e;
    }
}

    function setStatus(message, type = '') {
      statusBox.textContent = message;
      statusBox.className = 'status' + (type ? ' ' + type : '');
    }

    function getSelectedPrinter() {
      return printerSelect.value;
    }

    function openLabel() {
      return dymo.label.framework.openLabelXml(LABEL_XML);
    }

    function updateLabelValues(label) {
      const barcode = barcodeValue.value.trim();
      const name = productName.value.trim();
      const price = priceValue.value.trim();

      if (!barcode) {
        throw new Error('Veuillez saisir une valeur pour BARCODE.');
      }

      label.setObjectText('BARCODE', barcode);
      label.setObjectText('NOM_PRODUIT', name || '');
      label.setObjectText('PRIX', price || '');
    }

    function loadPrinters() {
      try {
        const printers = dymo.label.framework.getPrinters() || [];
        const dymoPrinters = printers.filter(printer => {
          const type = (printer.printerType || '').toLowerCase();
          const name = (printer.name || '').toLowerCase();
          return type.includes('labelwriter') || name.includes('dymo');
        });

        printerSelect.innerHTML = '';

        if (dymoPrinters.length === 0) {
          setStatus('Aucune imprimante DYMO LabelWriter détectée.', 'error');
          const option = document.createElement('option');
          option.value = '';
          option.textContent = 'Aucune imprimante trouvée';
          printerSelect.appendChild(option);
          return;
        }

        dymoPrinters.forEach(printer => {
          const option = document.createElement('option');
          option.value = printer.name;
          option.textContent = printer.name;
          printerSelect.appendChild(option);
        });

        setStatus('Imprimante DYMO détectée. Vous pouvez générer un aperçu ou imprimer.', 'ok');
      } catch (error) {
        setStatus('Impossible de charger les imprimantes DYMO : ' + error.message, 'error');
      }
    }

function renderPreview() {
  try {
    const label = openLabel();
    updateLabelValues(label);

    const printerName = getSelectedPrinter();
    const renderParamsXml = "";

    const pngData = label.render(renderParamsXml, printerName);

    previewZone.innerHTML = "";
    const img = document.createElement("img");
    img.src = "data:image/png;base64," + pngData;
    img.alt = "Aperçu de l’étiquette DYMO";
    previewZone.appendChild(img);

    setStatus("Aperçu généré.", "ok");
  } catch (error) {
    previewZone.innerHTML = '<div class="muted">Impossible de générer l’aperçu.</div>';
    setStatus("Erreur d’aperçu : " + error.message, "error");
    console.error(error);
  }
}

function printLabel() {
  try {
    const printerName = getSelectedPrinter();
    if (!printerName) {
      throw new Error("Aucune imprimante DYMO sélectionnée.");
    }

    const copies = Number(copiesInput.value) || 1;
    const label = openLabel();
    updateLabelValues(label);

    const printParamsXml = `
      <LabelWriterPrintParams>
        <Copies>${copies}</Copies>
      </LabelWriterPrintParams>
    `;

    label.print(printerName, printParamsXml, "");
    setStatus("Impression envoyée à " + printerName + ".", "ok");
  } catch (error) {
    setStatus("Erreur d’impression : " + error.message, "error");
    console.error(error);
  }
}

    function initDymo() {
      try {
        if (!window.dymo || !dymo.label || !dymo.label.framework) {
          setStatus('Le framework DYMO n’est pas chargé.', 'error');
          return;
        }

        dymo.label.framework.init(() => {
          loadPrinters();
          renderPreview();
        });
      } catch (error) {
        setStatus('Initialisation DYMO impossible : ' + error.message, 'error');
      }
    }

    refreshBtn.addEventListener('click', loadPrinters);
    previewBtn.addEventListener('click', renderPreview);
    printBtn.addEventListener('click', printLabel);
    barcodeValue.addEventListener('input', renderPreview);
    productName.addEventListener('input', renderPreview);
    priceValue.addEventListener('input', renderPreview);

    initDymo();
  </script>
</body>
</html>

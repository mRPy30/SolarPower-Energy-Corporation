<?php
/**
 * quotation_export.php
 * Generates an Excel quotation file matching the JHG template format.
 * Called via POST from quotation_builder.php
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die('Unauthorized');
}

// ── Collect POST data ────────────────────────────────────────────
$client_name     = trim($_POST['client_name'] ?? 'Client');
$email           = trim($_POST['email'] ?? '');
$contact         = trim($_POST['contact'] ?? '');
$location        = trim($_POST['location'] ?? '');
$system_type     = trim($_POST['system_type'] ?? 'HYBRID');
$kw              = floatval($_POST['kw'] ?? 0);
$officer         = trim($_POST['officer'] ?? '');
$q_number        = trim($_POST['quotation_number'] ?? ('Q-' . date('Y') . '-001'));
$q_date          = trim($_POST['quotation_date'] ?? date('F j, Y'));
$install_fee     = floatval($_POST['install_fee'] ?? 9000);
$installer_fee   = floatval($_POST['installer_fee'] ?? 4000);
$delivery        = floatval($_POST['delivery'] ?? 10000);
$discount_pct    = floatval($_POST['discount_pct'] ?? 15);
$formula_type    = trim($_POST['formula_type'] ?? 'standard');
$items_json      = $_POST['items'] ?? '[]';
$items           = json_decode($items_json, true) ?: [];

// ── Write Python script to tmp ───────────────────────────────────
$items_encoded = addslashes(json_encode($items));

$py_script = <<<PYTHON
import sys, json, os
import openpyxl
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side, numbers
from openpyxl.utils import get_column_letter

# ── Data ────────────────────────────────────────────────────────
client_name  = """ . json_encode($client_name) . """
email        = """ . json_encode($email) . """
contact      = """ . json_encode($contact) . """
location     = """ . json_encode($location) . """
system_type  = """ . json_encode($system_type) . """
kw           = $kw
officer      = """ . json_encode($officer) . """
q_number     = """ . json_encode($q_number) . """
q_date       = """ . json_encode(!empty($q_date) ? date('F j, Y', strtotime($q_date)) : date('F j, Y')) . """
install_fee  = $install_fee
installer_fee = $installer_fee
delivery     = $delivery
discount_pct = $discount_pct
formula_type = """ . json_encode($formula_type) . """
items        = """ . json_encode($items) . """

# ── Helpers ─────────────────────────────────────────────────────
def peso(n):
    return f"₱{n:,.2f}"

def solid(hex_color):
    return PatternFill("solid", fgColor=hex_color)

def font(name="Arial", size=10, bold=False, color="000000", italic=False):
    return Font(name=name, size=size, bold=bold, color=color, italic=italic)

def border(style="thin", color="CCCCCC"):
    s = Side(style=style, color=color)
    return Border(left=s, right=s, top=s, bottom=s)

def border_bottom(style="thin", color="CCCCCC"):
    return Border(bottom=Side(style=style, color=color))

def align(h="left", v="center", wrap=False):
    return Alignment(horizontal=h, vertical=v, wrap_text=wrap)

# ── Workbook ─────────────────────────────────────────────────────
wb = Workbook()
ws = wb.active
ws.title = "Quotation"

# Column widths (matching JHG template)
col_widths = {1:4, 2:40, 3:16, 4:6, 5:8, 6:14, 7:14, 8:14, 9:14, 10:4}
for c, w in col_widths.items():
    ws.column_dimensions[get_column_letter(c)].width = w

# Row heights
for r in range(1, 200):
    ws.row_dimensions[r].height = 16

ws.row_dimensions[1].height = 50
ws.row_dimensions[2].height = 18
ws.row_dimensions[3].height = 18

# ── HEADER SECTION ───────────────────────────────────────────────
# Row 1: Company name block
ws.merge_cells("A1:C1")
ws["A1"] = "SOLARPOWER ENERGY CORPORATION"
ws["A1"].font = font("Arial", 14, True, "FFFFFF")
ws["A1"].fill = solid("1E293B")
ws["A1"].alignment = align("center", "center")

ws.merge_cells("D1:J1")
ws["D1"] = (
    "Address: Ayala Alabang, Muntinlupa City\n"
    "Contact Number: 0995-234-6995 / 0995-394-7379\n"
    "FB Page: SOLARPOWER ENERGY CORPORATION"
)
ws["D1"].font = font("Arial", 9)
ws["D1"].fill = solid("F8FAFC")
ws["D1"].alignment = align("left", "center", True)
ws.row_dimensions[1].height = 55

# Row 2-3: Client info
ws.merge_cells("A2:D2")
ws["A2"] = f"Client Name: {client_name}"
ws["A2"].font = font("Arial", 10, True)
ws.merge_cells("E2:G2")
ws["E2"] = f"Date: {q_date}"
ws["E2"].font = font("Arial", 10)

ws.merge_cells("A3:D3")
ws["A3"] = f"Project Address: {location}"
ws["A3"].font = font("Arial", 10)
ws.merge_cells("E3:G3")
ws["E3"] = f"Quotation Number: {q_number}"
ws["E3"].font = font("Arial", 10)

ws.merge_cells("A4:D4")
ws["A4"] = f"Project Name: {kw}kW {system_type} System"
ws["A4"].font = font("Arial", 10, True, "0EA5E9")
ws.merge_cells("E4:G4")
ws["E4"] = f"Officer: {officer}"
ws["E4"].font = font("Arial", 10)

if email or contact:
    ws.merge_cells("A5:J5")
    ws["A5"] = f"Email: {email}   |   Contact: {contact}"
    ws["A5"].font = font("Arial", 9, False, "64748B")

# ── SPACER ───────────────────────────────────────────────────────
row = 7

# ── MATERIALS TABLE HEADER ───────────────────────────────────────
hdr_fill = solid("1E293B")
hdr_font = font("Arial", 9, True, "FFFFFF")
headers = ["#", "DESCRIPTION", "PHOTO REF", "QTY", "UNIT", "SRP (₱)", "MARKUP (₱)", "UNIT PRICE (₱)", "TOTAL AMOUNT (₱)", ""]

for col_idx, hdr in enumerate(headers, 1):
    cell = ws.cell(row=row, column=col_idx, value=hdr)
    cell.font = hdr_font
    cell.fill = hdr_fill
    cell.alignment = align("center", "center")
    cell.border = border("thin", "334155")

ws.row_dimensions[row].height = 20

# ── ITEM ROWS ────────────────────────────────────────────────────
row += 1
data_start = row
total_cols_end = 9

for idx, item in enumerate(items, 1):
    srp = float(item.get("srp", 0))
    markup = float(item.get("markup", 0))
    qty = float(item.get("qty", 1))
    unit_price = srp + markup
    total_row = unit_price * qty

    alt_fill = solid("F8FAFC") if idx % 2 == 0 else solid("FFFFFF")
    
    row_data = [
        idx,
        item.get("desc", ""),
        item.get("supplier", ""),
        qty,
        item.get("unit", "pcs"),
        srp,
        markup,
        unit_price,
        total_row,
        ""
    ]
    
    for col_idx, val in enumerate(row_data, 1):
        cell = ws.cell(row=row, column=col_idx, value=val)
        cell.fill = alt_fill
        cell.border = border("thin", "E2E8F0")
        
        if col_idx == 1:  # #
            cell.font = font("Arial", 9, False, "64748B")
            cell.alignment = align("center", "center")
        elif col_idx == 2:  # Description
            cell.font = font("Arial", 10, True)
            cell.alignment = align("left", "center", True)
        elif col_idx == 3:  # Photo ref
            cell.font = font("Arial", 9, False, "64748B")
            cell.alignment = align("center", "center")
        elif col_idx == 4:  # Qty
            cell.font = font("Arial", 10, True)
            cell.alignment = align("center", "center")
            cell.number_format = "#,##0"
        elif col_idx == 5:  # Unit
            cell.font = font("Arial", 9)
            cell.alignment = align("center", "center")
        elif col_idx in [6, 7]:  # SRP, Markup
            cell.font = font("Arial", 10, False, "0369A1")
            cell.alignment = align("right", "center")
            cell.number_format = "#,##0.00"
        elif col_idx == 8:  # Unit price
            cell.font = font("Arial", 10, True, "0EA5E9")
            cell.alignment = align("right", "center")
            cell.number_format = "#,##0.00"
        elif col_idx == 9:  # Total
            cell.font = font("Arial", 10, True)
            cell.alignment = align("right", "center")
            cell.number_format = "#,##0.00"
    
    ws.row_dimensions[row].height = 18
    row += 1

data_end = row - 1

# ── SUBTOTAL ROW ─────────────────────────────────────────────────
row += 1
sub_row = row
ws.merge_cells(f"A{row}:G{row}")
ws[f"A{row}"] = "MATERIALS SUBTOTAL"
ws[f"A{row}"].font = font("Arial", 10, True)
ws[f"A{row}"].fill = solid("F1F5F9")
ws[f"A{row}"].alignment = align("right", "center")

ws[f"H{row}"] = ""
sub_col = f"I{row}"
ws[sub_col] = f"=SUM(I{data_start}:I{data_end})"
ws[sub_col].font = font("Arial", 12, True, "1E293B")
ws[sub_col].fill = solid("F1F5F9")
ws[sub_col].alignment = align("right", "center")
ws[sub_col].number_format = "#,##0.00"
ws.row_dimensions[row].height = 22

# ── INSTALLATION / FEES ──────────────────────────────────────────
row += 2
ws.merge_cells(f"A{row}:C{row}")
ws[f"A{row}"] = "INSTALLATION FEE (OFC)"
ws[f"A{row}"].font = font("Arial", 10, True)
ws[f"D{row}"] = f"₱{install_fee:,.0f}/kW"
ws[f"D{row}"].font = font("Arial", 10)
ws[f"E{row}"] = f"{kw} kW"
ws[f"E{row}"].font = font("Arial", 10, True)
ws[f"I{row}"] = install_fee * kw
ws[f"I{row}"].font = font("Arial", 10, True, "10B981")
ws[f"I{row}"].number_format = "#,##0.00"
ws[f"I{row}"].alignment = align("right", "center")

row += 1
ws.merge_cells(f"A{row}:C{row}")
ws[f"A{row}"] = "INSTALLER FEE"
ws[f"A{row}"].font = font("Arial", 10, True)
ws[f"D{row}"] = f"₱{installer_fee:,.0f}/kW"
ws[f"D{row}"].font = font("Arial", 10)
ws[f"E{row}"] = f"{kw} kW"
ws[f"E{row}"].font = font("Arial", 10, True)
ws[f"I{row}"] = installer_fee * kw
ws[f"I{row}"].font = font("Arial", 10, True, "10B981")
ws[f"I{row}"].number_format = "#,##0.00"
ws[f"I{row}"].alignment = align("right", "center")

row += 1
ws.merge_cells(f"A{row}:C{row}")
ws[f"A{row}"] = "DELIVERY CHARGE"
ws[f"A{row}"].font = font("Arial", 10, True)
ws[f"I{row}"] = delivery
ws[f"I{row}"].font = font("Arial", 10, True, "10B981")
ws[f"I{row}"].number_format = "#,##0.00"
ws[f"I{row}"].alignment = align("right", "center")

row += 1
ws.merge_cells(f"A{row}:C{row}")
discount_label = f"DISCOUNT ({discount_pct:.1f}%)"
ws[f"A{row}"] = discount_label
ws[f"A{row}"].font = font("Arial", 10, True, "EF4444")
disc_cell = f"I{row}"
ws[disc_cell] = f"=-{discount_pct/100}*I{sub_row}"
ws[disc_cell].font = font("Arial", 10, True, "EF4444")
ws[disc_cell].number_format = "#,##0.00"
ws[disc_cell].alignment = align("right", "center")

# Grand Total
row += 2
gt_row = row
ws.merge_cells(f"A{row}:H{row}")
ws[f"A{row}"] = "GRAND TOTAL"
ws[f"A{row}"].font = font("Arial", 14, True, "FFFFFF")
ws[f"A{row}"].fill = solid("1E293B")
ws[f"A{row}"].alignment = align("right", "center")

install_row = sub_row + 2
deliv_row = sub_row + 4
disc_row = sub_row + 5

# Build grand total formula based on formula_type
gt_cell = f"I{row}"
if formula_type == "with_delivery":
    ws[gt_cell] = f"=I{sub_row}+I{install_row}+I{install_row+1}+I{deliv_row}+I{disc_row}"
elif formula_type == "with_vat":
    ws[gt_cell] = f"=(I{sub_row}+I{install_row}+I{install_row+1}+I{disc_row})*1.12"
else:  # standard
    ws[gt_cell] = f"=I{sub_row}+I{install_row}+I{install_row+1}+I{disc_row}"

ws[gt_cell].font = font("Arial", 14, True, "F59E0B")
ws[gt_cell].fill = solid("1E293B")
ws[gt_cell].alignment = align("right", "center")
ws[gt_cell].number_format = "₱#,##0.00"
ws.row_dimensions[row].height = 28

# Downpayment row
row += 1
ws.merge_cells(f"A{row}:H{row}")
ws[f"A{row}"] = "50% DOWNPAYMENT"
ws[f"A{row}"].font = font("Arial", 10, True)
ws[f"A{row}"].fill = solid("FEF3C7")
ws[f"A{row}"].alignment = align("right", "center")
ws[f"I{row}"] = f"=I{gt_row}*0.5"
ws[f"I{row}"].font = font("Arial", 11, True, "B45309")
ws[f"I{row}"].fill = solid("FEF3C7")
ws[f"I{row}"].number_format = "₱#,##0.00"
ws[f"I{row}"].alignment = align("right", "center")
ws.row_dimensions[row].height = 22

# ── TERMS SECTION ────────────────────────────────────────────────
row += 3
ws.merge_cells(f"A{row}:J{row}")
ws[f"A{row}"] = "TERMS OF PAYMENT"
ws[f"A{row}"].font = font("Arial", 11, True)
ws[f"A{row}"].fill = solid("F1F5F9")

row += 1
ws.merge_cells(f"A{row}:J{row}")
ws[f"A{row}"] = "LABOR & MATERIALS"
ws[f"A{row}"].font = font("Arial", 10, True)

terms = [
    "50% DOWNPAYMENT UPON CONTRACT SIGNING",
    "20% PROGRESS BILLING UPON DELIVERY OF MAJOR MATERIALS AND MOUNTING WORKS",
    "20% PROGRESS BILLING UPON SYSTEM INSTALLATION AND COMMISSIONING",
    "10% RETENTION PAYABLE AFTER FINAL TESTING AND PROJECT TURNOVER"
]
for t in terms:
    row += 1
    ws.merge_cells(f"A{row}:J{row}")
    ws[f"A{row}"] = f"• {t}"
    ws[f"A{row}"].font = font("Arial", 9, False, "64748B")

# ── NOTES SECTION ────────────────────────────────────────────────
row += 2
ws.merge_cells(f"A{row}:J{row}")
ws[f"A{row}"] = "NOTES"
ws[f"A{row}"].font = font("Arial", 11, True)
ws[f"A{row}"].fill = solid("F1F5F9")

notes = [
    "*PRICES ARE SUBJECT TO CHANGE WITHOUT PRIOR NOTICE.",
    "*OTHER MATERIALS NOT SPECIFIED IN THE QUOTATION ARE SUBJECT FOR ADDITIONAL ORDER.",
    "*QUOTATION PRICE IS VALID FOR 15 DAYS FROM THE DATE OF SENDING",
    "*THIS QUOTATION IS VAT EXCLUSIVE",
    "*PRODUCT WARRANTIES: Solar Panel - 12 Years | Inverter - 5-10 Years | Battery - 5 Years | Workmanship - 2 Years"
]
for n in notes:
    row += 1
    ws.merge_cells(f"A{row}:J{row}")
    ws[f"A{row}"] = n
    ws[f"A{row}"].font = font("Arial", 9, False, "64748B")

# ── SIGNATURE SECTION ────────────────────────────────────────────
row += 3
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = "Prepared by:"
ws[f"A{row}"].font = font("Arial", 10, True)
ws.merge_cells(f"E{row}:F{row}")
ws[f"E{row}"] = "Accepted By:"
ws[f"E{row}"].font = font("Arial", 10, True)

row += 1
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = officer
ws[f"A{row}"].font = font("Arial", 10, True, "0EA5E9")
ws.merge_cells(f"E{row}:F{row}")
ws[f"E{row}"] = client_name
ws[f"E{row}"].font = font("Arial", 10, True)

row += 1
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = "Sales Officer"
ws[f"A{row}"].font = font("Arial", 9, False, "64748B")
ws.merge_cells(f"E{row}:F{row}")
ws[f"E{row}"] = "Client / Owner"
ws[f"E{row}"].font = font("Arial", 9, False, "64748B")

row += 3
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = "Checked & Approved By:"
ws[f"A{row}"].font = font("Arial", 10, True)

row += 1
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = "Kenneth James L. Pares"
ws[f"A{row}"].font = font("Arial", 10, True)

row += 1
ws.merge_cells(f"A{row}:B{row}")
ws[f"A{row}"] = "VP For Operations"
ws[f"A{row}"].font = font("Arial", 9, False, "64748B")

# ── PRINT SETTINGS ───────────────────────────────────────────────
ws.page_setup.orientation = ws.ORIENTATION_PORTRAIT
ws.page_setup.paperSize = ws.PAPERSIZE_A4
ws.page_margins.left = 0.5
ws.page_margins.right = 0.5
ws.page_margins.top = 0.75
ws.page_margins.bottom = 0.75
ws.print_title_rows = "1:1"

# ── SAVE ────────────────────────────────────────────────────────
outfile = "/tmp/quotation_output.xlsx"
wb.save(outfile)
print("SUCCESS:" + outfile)
PYTHON;

// Write python script
$py_file = tempnam(sys_get_temp_dir(), 'qexp_') . '.py';
file_put_contents($py_file, $py_script);

// Execute
exec("python3 " . escapeshellarg($py_file) . " 2>&1", $output, $return_code);
unlink($py_file);

$output_str = implode("\n", $output);

if ($return_code !== 0 || !str_contains($output_str, 'SUCCESS:')) {
    // Return error page
    header('Content-Type: text/html');
    echo '<pre>Export Error: ' . htmlspecialchars($output_str) . '</pre>';
    exit;
}

$outfile = '/tmp/quotation_output.xlsx';
if (!file_exists($outfile)) {
    die('Output file not found');
}

// Clean filename
$safe_client = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $client_name);
$filename = 'Quotation_' . $safe_client . '_' . date('Ymd') . '_' . str_replace('-', '', $q_number) . '.xlsx';

// Send file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($outfile));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($outfile);
unlink($outfile);
exit;
#!/usr/bin/env python3
"""
Convertit tous les fichiers BIFF5 .XLS en .xlsx
dans le dossier database/seeders/data/
"""
import os, sys, xlrd, openpyxl

data_dir = os.path.join(os.path.dirname(__file__), 'data')

converted = 0
errors = 0

for root, dirs, files in os.walk(data_dir):
    for fname in files:
        if not fname.upper().endswith('.XLS'):
            continue
        src = os.path.join(root, fname)
        dst = src[:-4] + '.xlsx'  # remplace .XLS par .xlsx

        try:
            wb_in = xlrd.open_workbook(src)
            ws_in = wb_in.sheet_by_index(0)

            wb_out = openpyxl.Workbook()
            ws_out = wb_out.active

            for r in range(ws_in.nrows):
                for c in range(ws_in.ncols):
                    ws_out.cell(row=r+1, column=c+1, value=ws_in.cell_value(r, c))

            wb_out.save(dst)
            print(f'✓ {os.path.relpath(dst, data_dir)}')
            converted += 1

        except Exception as e:
            print(f'✗ {fname} → {e}')
            errors += 1

print(f'\nConvertis : {converted} | Erreurs : {errors}')

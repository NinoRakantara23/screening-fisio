# -*- coding: utf-8 -*-
"""Generator file ERD FootScreen Pro (.drawio) berdasarkan skema database proyek."""
import os

OUT = r"D:\screening-fisio\docs\ERD-FootScreen.drawio"

HDR_H = 30
ROW_H = 22

STYLE_HDR = "rounded=0;whiteSpace=wrap;html=1;fillColor=#0E1419;strokeColor=#00E676;fontColor=#00E676;fontStyle=1;fontSize=13;align=center;verticalAlign=middle;"
STYLE_N   = "rounded=0;whiteSpace=wrap;html=1;fillColor=#141B22;strokeColor=#1C252F;fontColor=#E8F0F7;fontSize=10;align=left;verticalAlign=middle;spacingLeft=8;"
STYLE_PK  = "rounded=0;whiteSpace=wrap;html=1;fillColor=#0A1F2F;strokeColor=#8AC7FF;fontColor=#8AC7FF;fontStyle=1;fontSize=10;align=left;verticalAlign=middle;spacingLeft=8;"
STYLE_FK  = "rounded=0;whiteSpace=wrap;html=1;fillColor=#1F1A0A;strokeColor=#FFD740;fontColor=#FFD740;fontSize=10;align=left;verticalAlign=middle;spacingLeft=8;"

# kolom = [(nama, tipe/keterangan, kind)]  kind: 'pk' | 'fk' | 'n'
COLS = {}

COLS['users'] = [
    ('id', 'BIGINT - PK', 'pk'),
    ('name', 'VARCHAR', 'n'),
    ('email', 'VARCHAR - UNIQUE', 'n'),
    ('password', 'VARCHAR (bcrypt)', 'n'),
    ('phone', 'VARCHAR(20) - UNIQUE', 'n'),
    ('role', "VARCHAR - admin|fisio|pelatih|pemain", 'n'),
    ('athlete_id', 'FK -> athletes.id - nullable', 'fk'),
    ('remember_token', 'VARCHAR', 'n'),
    ('created_at', 'TIMESTAMP', 'n'),
    ('updated_at', 'TIMESTAMP', 'n'),
]

COLS['athletes'] = [
    ('id', 'BIGINT - PK', 'pk'),
    ('name', 'VARCHAR', 'n'),
    ('birth_place', 'VARCHAR - nullable', 'n'),
    ('birth_date', 'DATE - nullable', 'n'),
    ('age', 'INTEGER - nullable', 'n'),
    ('gender', 'VARCHAR (putra/putri) - nullable', 'n'),
    ('position', 'VARCHAR (Striker/Gelandang...) - nullable', 'n'),
    ('height', 'DECIMAL(5,1) cm - nullable', 'n'),
    ('weight', 'DECIMAL(5,1) kg - nullable', 'n'),
    ('phone', 'VARCHAR(20) - nullable', 'n'),
    ('photo', 'VARCHAR - path foto', 'n'),
    ('notes', 'TEXT - nullable', 'n'),
    ('is_active', 'BOOLEAN default true', 'n'),
    ('created_at', 'TIMESTAMP', 'n'),
    ('updated_at', 'TIMESTAMP', 'n'),
    ('deleted_at', 'TIMESTAMP (soft delete)', 'n'),
]

COLS['test_results'] = [
    ('id', 'BIGINT - PK', 'pk'),
    ('user_id', 'FK -> users.id (fisio)', 'fk'),
    ('athlete_id', 'FK -> athletes.id', 'fk'),
    ('test_type', 'endurance|speed|agility|strength|power|accuracy|reaction|coordination|flexibility|balance', 'n'),
    ('name', 'VARCHAR (identitas historis)', 'n'),
    ('age', 'INTEGER - nullable', 'n'),
    ('gender', 'VARCHAR - nullable', 'n'),
    ('position', 'VARCHAR - nullable', 'n'),
    ('height', 'DECIMAL(5,1) - nullable', 'n'),
    ('weight', 'DECIMAL(5,1) - nullable', 'n'),
    ('phone', 'VARCHAR(20) - nullable', 'n'),
    ('test_date', 'DATETIME', 'n'),
    ('vital_td', 'VARCHAR (tekanan darah)', 'n'),
    ('vital_suhu', 'VARCHAR (suhu)', 'n'),
    ('vital_nadi', 'VARCHAR (nadi)', 'n'),
    ('vital_respirasi', 'VARCHAR (respirasi)', 'n'),
    ('complaint', 'TEXT - nullable', 'n'),
    ('distance', 'DECIMAL(8,2) m - Endurance', 'n'),
    ('level', 'VARCHAR (level YoYo)', 'n'),
    ('time_seconds', 'DECIMAL(6,2) - Speed', 'n'),
    ('score', 'INTEGER 1-5', 'n'),
    ('hex_time1', 'DECIMAL(6,2) - Agility', 'n'),
    ('hex_time2', 'DECIMAL(6,2)', 'n'),
    ('hex_avg', 'DECIMAL(6,2)', 'n'),
    ('hex_rating', 'VARCHAR', 'n'),
    ('ill_time', 'DECIMAL(6,2)', 'n'),
    ('ill_rating', 'VARCHAR', 'n'),
    ('pushup_count', 'INTEGER - Strength', 'n'),
    ('pushup_score', 'INTEGER 1-5', 'n'),
    ('pushup_rating', 'VARCHAR', 'n'),
    ('pushup_note', 'TEXT - nullable', 'n'),
    ('situp_count', 'INTEGER', 'n'),
    ('situp_score', 'INTEGER 1-5', 'n'),
    ('situp_rating', 'VARCHAR', 'n'),
    ('situp_note', 'TEXT - nullable', 'n'),
    ('try1', 'DECIMAL(7,2) - Power/Flex', 'n'),
    ('try2', 'DECIMAL(7,2)', 'n'),
    ('try3', 'DECIMAL(7,2)', 'n'),
    ('best', 'DECIMAL(7,2) cm', 'n'),
    ('shot1_score', 'DECIMAL(5,1) - Accuracy', 'n'),
    ('shot1_time', 'DECIMAL(5,2)', 'n'),
    ('shot1_note', 'VARCHAR', 'n'),
    ('shot2_score', 'DECIMAL(5,1)', 'n'),
    ('shot2_time', 'DECIMAL(5,2)', 'n'),
    ('shot2_note', 'VARCHAR', 'n'),
    ('shot3_score', 'DECIMAL(5,1)', 'n'),
    ('shot3_time', 'DECIMAL(5,2)', 'n'),
    ('shot3_note', 'VARCHAR', 'n'),
    ('total_score', 'DECIMAL(6,1)', 'n'),
    ('value_cm', 'DECIMAL(6,2) cm - Reaction', 'n'),
    ('steps50', 'DECIMAL(5,1) - Coordination', 'n'),
    ('steps50_result', 'VARCHAR (normal/abnormal)', 'n'),
    ('steps100', 'DECIMAL(5,1)', 'n'),
    ('steps100_result', 'VARCHAR', 'n'),
    ('rotation_direction', 'VARCHAR', 'n'),
    ('leg_length', 'DECIMAL(5,1) - Balance YBT', 'n'),
    ('left_a', 'DECIMAL(6,2) (anterior)', 'n'),
    ('left_pm', 'DECIMAL(6,2) (posteromedial)', 'n'),
    ('left_pl', 'DECIMAL(6,2) (posterolateral)', 'n'),
    ('right_a', 'DECIMAL(6,2)', 'n'),
    ('right_pm', 'DECIMAL(6,2)', 'n'),
    ('right_pl', 'DECIMAL(6,2)', 'n'),
    ('res_left_anterior', 'VARCHAR (hasil)', 'n'),
    ('res_right_anterior', 'VARCHAR', 'n'),
    ('res_diff_anterior', 'VARCHAR (selisih)', 'n'),
    ('res_left_pm', 'VARCHAR', 'n'),
    ('res_right_pm', 'VARCHAR', 'n'),
    ('res_diff_pm', 'VARCHAR', 'n'),
    ('res_left_pl', 'VARCHAR', 'n'),
    ('res_right_pl', 'VARCHAR', 'n'),
    ('res_diff_pl', 'VARCHAR', 'n'),
    ('composite_left', 'VARCHAR', 'n'),
    ('composite_right', 'VARCHAR', 'n'),
    ('std_anterior', 'VARCHAR (standar)', 'n'),
    ('std_pm', 'VARCHAR', 'n'),
    ('std_pl', 'VARCHAR', 'n'),
    ('std_composite', 'VARCHAR', 'n'),
    ('rating', 'VARCHAR (rating akhir)', 'n'),
    ('conclusion', 'TEXT - nullable', 'n'),
    ('created_at', 'TIMESTAMP', 'n'),
    ('updated_at', 'TIMESTAMP', 'n'),
    ('deleted_at', 'TIMESTAMP (soft delete)', 'n'),
]

COLS['mental_assessments'] = [
    ('id', 'BIGINT - PK', 'pk'),
    ('user_id', 'FK -> users.id (pelatih)', 'fk'),
    ('athlete_id', 'FK -> athletes.id', 'fk'),
    ('name', 'VARCHAR', 'n'),
    ('assessment_date', 'DATETIME', 'n'),
    ('confidence', 'INTEGER 1-5 (percaya diri)', 'n'),
    ('confidence_note', 'VARCHAR - nullable', 'n'),
    ('focus', 'INTEGER 1-5 (konsentrasi)', 'n'),
    ('focus_note', 'VARCHAR - nullable', 'n'),
    ('anxiety', 'INTEGER 1-5 (kecemasan)', 'n'),
    ('anxiety_note', 'VARCHAR - nullable', 'n'),
    ('motivation', 'INTEGER 1-5', 'n'),
    ('motivation_note', 'VARCHAR - nullable', 'n'),
    ('team_spirit', 'INTEGER 1-5 (semangat tim)', 'n'),
    ('team_spirit_note', 'VARCHAR - nullable', 'n'),
    ('pressure_handling', 'INTEGER 1-5 (tekanan)', 'n'),
    ('pressure_handling_note', 'VARCHAR - nullable', 'n'),
    ('overall_assessment', 'TEXT - nullable', 'n'),
    ('recommendations', 'TEXT - nullable', 'n'),
    ('created_at', 'TIMESTAMP', 'n'),
    ('updated_at', 'TIMESTAMP', 'n'),
    ('deleted_at', 'TIMESTAMP (soft delete)', 'n'),
]

COLS['taktik_assessments'] = [
    ('id', 'BIGINT - PK', 'pk'),
    ('user_id', 'FK -> users.id (pelatih)', 'fk'),
    ('athlete_id', 'FK -> athletes.id', 'fk'),
    ('name', 'VARCHAR', 'n'),
    ('assessment_date', 'DATETIME', 'n'),
    ('dribbling', 'INTEGER 1-5', 'n'),
    ('dribbling_note', 'VARCHAR - nullable', 'n'),
    ('passing', 'INTEGER 1-5', 'n'),
    ('passing_note', 'VARCHAR - nullable', 'n'),
    ('shooting', 'INTEGER 1-5', 'n'),
    ('shooting_note', 'VARCHAR - nullable', 'n'),
    ('positioning', 'INTEGER 1-5 (posisi lapangan)', 'n'),
    ('positioning_note', 'VARCHAR - nullable', 'n'),
    ('game_reading', 'INTEGER 1-5 (baca permainan)', 'n'),
    ('game_reading_note', 'VARCHAR - nullable', 'n'),
    ('decision_making', 'INTEGER 1-5 (keputusan)', 'n'),
    ('decision_making_note', 'VARCHAR - nullable', 'n'),
    ('defensive_skill', 'INTEGER 1-5 (bertahan)', 'n'),
    ('defensive_skill_note', 'VARCHAR - nullable', 'n'),
    ('set_pieces', 'INTEGER 1-5', 'n'),
    ('set_pieces_note', 'VARCHAR - nullable', 'n'),
    ('overall_assessment', 'TEXT - nullable', 'n'),
    ('recommendations', 'TEXT - nullable', 'n'),
    ('formation_notes', 'TEXT (formasi & taktik)', 'n'),
    ('created_at', 'TIMESTAMP', 'n'),
    ('updated_at', 'TIMESTAMP', 'n'),
    ('deleted_at', 'TIMESTAMP (soft delete)', 'n'),
]

LAYOUT = [
    ('users', 40, 90, 300),
    ('athletes', 40, 420, 300),
    ('test_results', 430, 90, 340),
    ('mental_assessments', 880, 90, 340),
    ('taktik_assessments', 880, 660, 340),
]

cells = []
cid = [2]  # counter


def next_id():
    v = cid[0]
    cid[0] += 1
    return v


header_ids = {}

for name, x, y, w in LAYOUT:
    cols = COLS[name]
    sub = {
        'users': 'Akun sistem (admin / fisio / pelatih / pemain)',
        'athletes': 'Data atlet (screening fisioterapi)',
        'test_results': 'Hasil tes fisik fisioterapi (10 jenis)',
        'mental_assessments': 'Assessment mental oleh pelatih',
        'taktik_assessments': 'Assessment taktik oleh pelatih',
    }[name]
    h = next_id()
    header_ids[name] = h
    cells.append(f'<mxCell id="{h}" value="{name} — {sub}" style="{STYLE_HDR}" vertex="1" parent="1"><mxGeometry x="{x}" y="{y}" width="{w}" height="{HDR_H}" as="geometry"/></mxCell>')
    yy = y + HDR_H
    for col, typ, kind in cols:
        rid = next_id()
        style = {'pk': STYLE_PK, 'fk': STYLE_FK, 'n': STYLE_N}[kind]
        mark = {'pk': '🔑', 'fk': '⚑', 'n': ' '}[kind]
        cells.append(f'<mxCell id="{rid}" value="{mark} {col}   {typ}" style="{style}" vertex="1" parent="1"><mxGeometry x="{x}" y="{yy}" width="{w}" height="{ROW_H}" as="geometry"/></mxCell>')
        yy += ROW_H

# ---- Edges / relasi ----
EDGES = [
    # (source_table, target_table, label, color, start, end, exitX, exitY, entryX, entryY)
    ('users', 'athletes', '1 : 1 (pemain)', '#40C4FF', 'ERone', 'ERone', 0.5, 1.0, 0.5, 0.0),
    ('users', 'test_results', '1 : N (fisio)', '#00E676', 'ERone', 'ERmany', 1.0, 0.35, 0.0, 0.10),
    ('athletes', 'test_results', '1 : N', '#00E676', 'ERone', 'ERmany', 1.0, 0.25, 0.0, 0.40),
    ('users', 'mental_assessments', '1 : N (pelatih)', '#FFB700', 'ERone', 'ERmany', 1.0, 0.55, 0.0, 0.10),
    ('athletes', 'mental_assessments', '1 : N', '#FFB700', 'ERone', 'ERmany', 1.0, 0.55, 0.0, 0.30),
    ('users', 'taktik_assessments', '1 : N (pelatih)', '#FF6B6B', 'ERone', 'ERmany', 1.0, 0.75, 0.0, 0.10),
    ('athletes', 'taktik_assessments', '1 : N', '#FF6B6B', 'ERone', 'ERmany', 1.0, 0.85, 0.0, 0.35),
]

for src, tgt, label, color, start, end, ex, ey, nx, ny in EDGES:
    eid = next_id()
    style = (f'endArrow={end};startArrow={start};html=1;rounded=0;strokeColor={color};'
             f'fontColor=#7A8D9E;fontSize=11;fontStyle=1;'
             f'exitX={ex};exitY={ey};exitDx=0;exitDy=0;entryX={nx};entryY={ny};entryDx=0;entryDy=0;')
    cells.append(f'<mxCell id="{eid}" value="{label}" style="{style}" edge="1" parent="1" source="{header_ids[src]}" target="{header_ids[tgt]}"><mxGeometry relative="1" as="geometry"/></mxCell>')

# ---- Judul & legenda ----
title_id = next_id()
cells.append(f'<mxCell id="{title_id}" value="ERD — FootScreen Pro · Sistem Screening Fisioterapi Sepak Bola (Back-End Database)" style="text;html=1;strokeColor=none;fillColor=none;align=center;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontSize=18;fontStyle=1;fontColor=#00E676;" vertex="1" parent="1"><mxGeometry x="40" y="20" width="1180" height="44" as="geometry"/></mxCell>')

legend_id = next_id()
cells.append(f'<mxCell id="{legend_id}" value="LEGENDA" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#0E1419;strokeColor=#00E676;fontColor=#00E676;fontStyle=1;fontSize=13;align=center;" vertex="1" parent="1"><mxGeometry x="40" y="2050" width="320" height="240" as="geometry"/></mxCell>')

leg = [
    ('🔑 Primary Key', '#8AC7FF'),
    ('⚑ Foreign Key', '#FFD740'),
    ('Kolom biasa (nullable)', '#E8F0F7'),
    ('1 : 1  Pemain → Atlet', '#40C4FF'),
    ('1 : N  Fisio → Hasil Tes', '#00E676'),
    ('1 : N  Pelatih → Mental', '#FFB700'),
    ('1 : N  Pelatih → Taktik', '#FF6B6B'),
    ('Semua FK pakai NULL ON DELETE', '#7A8D9E'),
]
yy = 2092
for text, color in leg:
    lid = next_id()
    cells.append(f'<mxCell id="{lid}" value="{text}" style="text;html=1;strokeColor=none;fillColor=none;align=left;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontSize=12;fontColor={color};fontStyle=1;" vertex="1" parent="1"><mxGeometry x="60" y="{yy}" width="280" height="22" as="geometry"/></mxCell>')
    yy += 24

xml_cells = "\n".join(cells)

xml = f'''<mxfile host="app.diagrams.net" agent="Claude Code" version="24.0.0" type="device">
  <diagram id="erd-footscreen" name="ERD FootScreen Pro">
    <mxGraphModel dx="1422" dy="762" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1600" pageHeight="2400" math="0" shadow="0">
      <root>
        <mxCell id="0"/>
        <mxCell id="1" parent="0"/>
{xml_cells}
      </root>
    </mxGraphModel>
  </diagram>
</mxfile>
'''

os.makedirs(os.path.dirname(OUT), exist_ok=True)
with open(OUT, 'w', encoding='utf-8') as f:
    f.write(xml)

print('OK ->', OUT)
print('cells:', cid[0] - 2)

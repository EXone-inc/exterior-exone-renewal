# コラム不変チェック用の画素比較（AC-05-5）。
# 使い方: python3 compare.py <ベースラインのディレクトリ> <比較するディレクトリ>
# 判定: サイズが同じ かつ 差が 40 を超える画素が 0 → same。
# （写真の縮小処理による撮影ごとの小さな揺らぎは許容し、レイアウト・文字・色の変化は検出する）
import sys, os
from PIL import Image, ImageChops

base, other = sys.argv[1], sys.argv[2]
ng = 0
for f in sorted(os.listdir(base)):
    if not f.endswith('.png'):
        continue
    a = Image.open(os.path.join(base, f)).convert('RGB')
    p = os.path.join(other, f)
    if not os.path.exists(p):
        print('MISSING', f); ng += 1; continue
    b = Image.open(p).convert('RGB')
    if a.size != b.size:
        print('SIZE', f, a.size, b.size); ng += 1; continue
    d = ImageChops.difference(a, b).convert('L')
    h = d.histogram()
    strong = sum(h[41:])
    box = d.point(lambda v: 255 if v > 40 else 0).getbbox()
    print(('same ' if strong == 0 else 'DIFF ') + f, 'changed>0:', sum(h[1:]), 'changed>40:', strong, 'bbox>40:', box)
    ng += strong > 0
sys.exit(1 if ng else 0)

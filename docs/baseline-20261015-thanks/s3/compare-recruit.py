# S3: /recruit/ の画素比較。粒子の canvas 矩形（jitter/canvas-rect.json）を除いた範囲と、全体の両方で数える。
# 使い方: python3 compare-recruit.py
import json, itertools
from PIL import Image, ImageChops
rect = json.load(open('jitter/canvas-rect.json'))
dpr = {'pc': 1, 'sp': 2}
def stat(a, b, mask=None):
    a = Image.open(a).convert('RGB'); b = Image.open(b).convert('RGB')
    if a.size != b.size: return ('SIZE', a.size, b.size)
    d = ImageChops.difference(a, b).convert('L')
    if mask:
        d.paste(0, mask)
    h = d.histogram()
    return ('changed>0', sum(h[1:]), 'changed>40', sum(h[41:]), 'bbox>40', d.point(lambda v: 255 if v > 40 else 0).getbbox())
for w in ('pc', 'sp'):
    s = dpr[w]
    l, t, r, btm = rect[w]
    m = (int(l * s), int(t * s), int(r * s + 1), int(btm * s + 1))
    files = ['../%s-recruit.png' % w] + ['%s-recruit.png' % w] + ['jitter/%s-recruit-%d.png' % (w, i) for i in (1, 2, 3)]
    names = ['baseline', 's3', 'j1', 'j2', 'j3']
    print('==', w, 'canvas mask', m)
    for (na, a), (nb, b) in itertools.combinations(zip(names, files), 2):
        print(na, 'vs', nb, 'full:', stat(a, b), '| canvas除外:', stat(a, b, m))

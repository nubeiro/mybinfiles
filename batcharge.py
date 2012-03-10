#!/usr/bin/env python
# coding=UTF-8

import sys, math, subprocess, re

p = subprocess.Popen(["acpi"], stdout=subprocess.PIPE)
output = p.communicate()[0]

pattern = re.compile(r"[0-9]{1,3}%")

match = pattern.search(output)
b_remaining = match.group(0)
b_remaining = int(b_remaining[:-1])
# Output
total_slots, slots = 10, []
filled = int(math.ceil(b_remaining / 10)) * u'▸'
empty = (total_slots - len(filled)) * u'▹'

out = (filled + empty).encode('utf-8')

color_green = '%{[32m%}'
color_yellow = '%{[1;33m%}'
color_red = '%{[31m%}'
color_reset = '%{[00m%}'
color_out = (
    color_green if len(filled) > 6
    else color_yellow if len(filled) > 4
    else color_red
)

out = color_out + out + color_reset
sys.stdout.write(out)
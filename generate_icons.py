#!/usr/bin/env python3
"""
Generate app icons for Excellence Academy PWA
Creates SVG icons and converts them to PNG format
"""

import os
from pathlib import Path

def create_svg_icon(size, filename):
    """Create an SVG icon with graduation cap design"""
    svg_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<svg width="{size}" height="{size}" viewBox="0 0 {size} {size}" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
    </linearGradient>
  </defs>
  
  <!-- Background Circle -->
  <circle cx="{size//2}" cy="{size//2}" r="{size//2}" fill="url(#gradient)"/>
  
  <!-- Graduation Cap -->
  <g transform="translate({size//2}, {size//2})">
    <!-- Cap Base -->
    <ellipse cx="0" cy="-{size//8}" rx="{size//3}" ry="{size//6}" fill="#fbbf24"/>
    
    <!-- Cap Top -->
    <polygon points="-{size//3},-{size//4} {size//3},-{size//4} {size//4},-{size//3} -{size//4},-{size//3}" fill="#f59e0b"/>
    
    <!-- Tassel -->
    <circle cx="{size//4}" cy="-{size//3}" r="{size//20}" fill="#ef4444"/>
    <line x1="{size//4}" y1="-{size//3}" x2="{size//3}" y2="-{size//6}" stroke="#ef4444" stroke-width="{size//40}"/>
    
    <!-- Book -->
    <rect x="-{size//6}" y="{size//12}" width="{size//3}" height="{size//4}" rx="{size//40}" fill="white"/>
    <line x1="-{size//8}" y1="{size//6}" x2="{size//8}" y2="{size//6}" stroke="#2563eb" stroke-width="{size//60}"/>
    <line x1="-{size//8}" y1="{size//4}" x2="{size//8}" y2="{size//4}" stroke="#2563eb" stroke-width="{size//60}"/>
  </g>
</svg>'''
    
    with open(f"icons/{filename}", 'w') as f:
        f.write(svg_content)
    print(f"Created icons/{filename}")

def main():
    """Generate all required app icons"""
    # Create icons directory
    Path("icons").mkdir(exist_ok=True)
    
    # Icon sizes for PWA
    sizes = [72, 96, 128, 144, 152, 192, 384, 512]
    
    print("Generating Excellence Academy app icons...")
    
    for size in sizes:
        filename = f"icon-{size}x{size}.svg"
        create_svg_icon(size, filename)
    
    print(f"Generated {len(sizes)} SVG icons successfully!")
    print("\nTo convert to PNG (if you have ImageMagick installed):")
    print("cd icons && for f in *.svg; do convert \"$f\" \"${f%.svg}.png\"; done")
    print("\nOr use online SVG to PNG converters for each file.")
    
    # Create a simple HTML preview
    html_content = '''<!DOCTYPE html>
<html>
<head>
    <title>Excellence Academy Icons Preview</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .icon-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; }
        .icon-item { text-align: center; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        .icon-item img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <h1>Excellence Academy - App Icons Preview</h1>
    <div class="icon-grid">'''
    
    for size in sizes:
        html_content += f'''
        <div class="icon-item">
            <img src="icon-{size}x{size}.svg" alt="{size}x{size}">
            <p>{size}x{size}</p>
        </div>'''
    
    html_content += '''
    </div>
</body>
</html>'''
    
    with open("icons/preview.html", 'w') as f:
        f.write(html_content)
    
    print("\nCreated icons/preview.html to view all icons in browser")

if __name__ == "__main__":
    main()
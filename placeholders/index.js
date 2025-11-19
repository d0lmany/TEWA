const express = require('express');
const { createCanvas } = require('canvas');
const app = express();
const port = 8001;

app.get('/storage/:dimensions.png/:color', (req, res) => {
    generateImage(req, res, req.params.color);
});

app.get('/storage/:dimensions.png', (req, res) => {
    generateImage(req, res, 'cccccc');
});

function generateImage(req, res, color) {
    try {
        const dimensions = req.params.dimensions;
        const text = req.query.text || dimensions;

        const [width, height] = dimensions.split('x').map(Number);
        
        if (!width || !height || width > 2000 || height > 2000) {
            return res.status(400).send('Invalid dimensions');
        }

        const canvas = createCanvas(width, height);
        const ctx = canvas.getContext('2d');

        ctx.fillStyle = `#${color}`;
        ctx.fillRect(0, 0, width, height);

        ctx.fillStyle = '#ffffff';
        ctx.font = `bold ${Math.min(width, height) / 10}px Arial`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        const words = text.split('+').join(' ').split(' ');
        const lines = [];
        let currentLine = words[0];
        
        for (let i = 1; i < words.length; i++) {
            const testLine = currentLine + ' ' + words[i];
            if (ctx.measureText(testLine).width < width * 0.8) {
                currentLine = testLine;
            } else {
                lines.push(currentLine);
                currentLine = words[i];
            }
        }
        lines.push(currentLine);

        const lineHeight = Math.min(width, height) / 8;
        const startY = height / 2 - (lines.length - 1) * lineHeight / 2;
        
        lines.forEach((line, index) => {
            ctx.fillText(line, width / 2, startY + index * lineHeight);
        });

        res.set({
            'Content-Type': 'image/png',
            'Cache-Control': 'public, max-age=31536000'
        });
        
        canvas.pngStream().pipe(res);

    } catch (error) {
        console.error('Error generating image:', error);
        res.status(500).send('Server error');
    }
}

app.listen(port, '127.0.0.1', () => {
    console.log(`image server running at http://127.0.0.1:${port}/storage/`);
    console.log(`test URL: http://127.0.0.1:${port}/storage/400x400.png/00cc22?text=product+aliquid+ratione+quis+facere`);
});
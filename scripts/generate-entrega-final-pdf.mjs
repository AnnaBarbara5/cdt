import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from '@playwright/test';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const htmlName = '2026-GR-01-Documentacao-Entrega-Final-Infraestrutura-AWS.html';
const htmlPath = path.join(root, 'docs', htmlName);
const pdfPath = path.join(
  root,
  'docs',
  '2026-GR-01-Documentacao-Entrega-Final-Infraestrutura-AWS.pdf',
);

const fileUrl = `file:///${htmlPath.replace(/\\/g, '/')}`;

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(fileUrl, { waitUntil: 'networkidle' });
await page.emulateMedia({ media: 'print' });
await page.pdf({
  path: pdfPath,
  format: 'A4',
  printBackground: true,
  margin: { top: '12mm', right: '14mm', bottom: '16mm', left: '14mm' },
});
await browser.close();

console.log(`PDF gerado: ${pdfPath}`);
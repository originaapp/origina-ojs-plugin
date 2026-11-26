# ORIGINA Plugin for OJS 3.3 / 3.4

Connect your Open Journal Systems (OJS 3.3 / 3.4) installation directly to **ORIGINA (`https://origina.app`)** for:
- 24-Language Plagiarism & Cross-Lingual Similarity Detection
- ONNX Multilingual AI Writing Detection
- Cheat Shield™ Forensics (Homoglyphs, Zero-Width Chars, Hidden Text)
- Cryptographically Signed Originality Certificates

## Installation
1. Generate a journal API token (`org_live_...`) at **https://origina.app/ojs-integration.html**.
2. In your OJS Dashboard, go to **Settings → Website → Plugins → Installed Plugins → Upload A New Plugin**.
3. Upload `origina-ojs3-plugin.tar.gz`.
4. Enable **ORIGINA Plagiarism & AI Integrity Check** under *Generic Plugins*.
5. Click **Settings**, enter `https://origina.app` and your Bearer API token, then save.

## REST API Endpoints
- `GET  /api/v1/ojs/ping` — Verify API key and remaining credits
- `POST /api/v1/ojs/submit` — Submit manuscript (`document` file field) to async worker
- `GET  /api/v1/ojs/status/{job_id}` — Poll progress and receive scores + report links
- `GET  /api/v1/ojs/report/{submission_id}` — Fetch structured report summary

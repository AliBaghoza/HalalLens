# HalalLens AI (عدسة الحلال الذكية)

**HalalLens AI** is a university AI Engineering platform designed to verify the Halal compliance of food products by analyzing ingredient label images through a backend-orchestrated Multi-Agent AI architecture.

---

## 📌 Project Overview

Unlike single-prompt wrappers or disconnected mockups, HalalLens AI implements a collaborative multi-agent architecture where specialized AI agents cooperate through a real server-side data pipeline:

1. **Ingredient Vision Agent (Agent 1)**: Ingests the raw product image via Gemini Vision API (`gemini-3.8-flash`), assesses source quality (`good`, `fair`, `poor`, `unreadable`), and extracts individual ingredients into a structured JSON contract (`original_name`, `normalized_name`, `confidence`) preserving packaging wording and uncertainty without making dietary rulings.
2. **Halal Verification Agent (Agent 2)**: Ingests the structured ingredient JSON directly from Agent 1, applies Islamic dietary jurisprudence and food science classification rules, and generates a canonical assessment report.
3. **Halal Restaurant Finder (Decoupled Service)**: An independent service querying OpenStreetMap Overpass and Nominatim APIs to discover nearby verified Halal dining amenities based on GPS coordinates or city search.

---

## 🏗️ Technology Stack

- **Backend**: PHP 8.2+ (Shared hosting / cPanel compatible)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (No Node.js, React, npm, Vite, or build tools)
- **Typography**: `Tajawal, Arial, sans-serif` universally applied as the visual brand identity
- **Networking**: PHP cURL with SSL verification and timeouts
- **AI Processing**: Google Gemini API via server-side REST integration
- **Geospatial & Mapping**: OpenStreetMap Overpass API & Nominatim Geocoding API

---

## 📁 Project Structure

```
HalalLens/
├── index.php                 # Main entry point (Arabic default, dir="rtl", Tajawal typography)
├── css/
│   └── style.css             # Universal Tajawal styling, RTL-first layout, Halal theme
├── js/
│   └── app.js                # Vanilla JS file handling, pipeline tracker & API interaction
├── lang/
│   ├── ar.php                # Default primary language dictionary (Arabic, RTL)
│   └── en.php                # Secondary language dictionary (English, LTR)
├── api/
│   ├── analyze.php           # Backend Multi-Agent Orchestrator (Agent 1 -> Agent 2 pipeline)
│   ├── verify.php            # Modular test endpoint for direct ingredient verification
│   ├── restaurants.php       # Decoupled OSM Overpass restaurant discovery service
│   └── geocode.php           # OSM Nominatim location search proxy
├── agents/
│   ├── IngredientAgent.php   # Agent 1: Vision OCR & ingredient extraction to structured JSON
│   └── HalalAgent.php        # Agent 2: Islamic dietary verification & rule evaluation
├── config/
│   └── config.example.php    # Configuration template with placeholders (no real secrets)
├── uploads/
│   ├── .gitkeep              # Directory tracker
│   └── .htaccess             # Apache rule denying direct script execution
├── assets/
│   └── .gitkeep              # Asset repository tracker
├── README.md                 # Project architecture & setup documentation
├── AGENTS.md                 # Detailed Multi-Agent collaboration specification
└── .gitignore                # Protects config.php, .env, and uploaded files
```

---

## 🔄 Backend Multi-Agent Orchestration Flow

The frontend does **NOT** orchestrate the Agent 1 → Agent 2 handoff. All collaboration occurs within the backend:

```
[User Uploads Image]
         │
         ▼ (HTTP POST multipart/form-data)
┌────────────────────────────────────────────────────────┐
│                   api/analyze.php                      │
│            (Backend Multi-Agent Orchestrator)          │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼
┌────────────────────────────────────────────────────────┐
│           Agent 1: Ingredient Vision Agent             │
│              (agents/IngredientAgent.php)              │
│       • Multimodal Vision processing via Gemini        │
│       • Extracts raw text & E-numbers                  │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼ (Structured JSON: {"ingredients": [...]})
┌────────────────────────────────────────────────────────┐
│           Agent 2: Halal Verification Agent            │
│                 (agents/HalalAgent.php)                │
│       • Cross-checks additives & animal sources        │
│       • Classifies into Canonical Statuses             │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼ (Combined Synthesized Payload)
┌────────────────────────────────────────────────────────┐
│                   Frontend (js/app.js)                 │
│         Renders pipeline tracker & final verdict       │
└────────────────────────────────────────────────────────┘
```

---

## 🏷️ Canonical Halal Terminology

HalalLens AI enforces four canonical uppercase statuses internally, localized into Arabic and English:

| Internal Canonical Status | Arabic Translation | English Translation | Criteria |
| :--- | :--- | :--- | :--- |
| `HALAL` | **حلال** | Halal | Strictly plant-based, aquatic, microbial, or certified Halal. |
| `HARAM` | **حرام** | Haram | Contains pork, porcine gelatin, non-dhabihah animal derivatives, intoxicants. |
| `DOUBTFUL` | **مشتبه فيه** | Doubtful | Questionable emulsifiers (e.g. E471), gelatin with unspecified source, enzymes. |
| `UNKNOWN` | **غير معروف** | Unknown | Illegible, corrupted label, or insufficient ingredient data. |

---

## 🔒 Security & Configuration Guidelines

### Server-Side Configuration

1. **Environment Variables (Recommended)**:
   Set the following variables in your server environment:
   ```bash
   export GEMINI_API_KEY="your-gemini-api-key"
   export GEMINI_MODEL="gemini-3.8-flash"
   ```

2. **File Fallback (`config/config.php`)**:
   On shared hosting or cPanel where environment variables are not easily set:
   - Copy `config/config.example.php` to `config/config.php`:
     ```bash
     cp config/config.example.php config/config.php
     ```
   - Edit `config/config.php` and set your credentials.
   - `config/config.php` is strictly excluded from version control via `.gitignore`.

### Upload Security

The `uploads/` directory includes an `.htaccess` file prohibiting script execution (`php_flag engine off`, denying `.php`, `.phtml`, `.cgi`, etc.) to prevent arbitrary code execution on shared hosting.

---

## 🚀 Local Development & Verification

To run HalalLens AI using PHP's built-in development server:

```powershell
# Start PHP built-in server from HalalLens root
php -S 127.0.0.1:8088
```

Open your browser at `http://127.0.0.1:8088`. The application will open in **Arabic (RTL)** with the **Tajawal** font. Click the language switcher in the header to switch to English (LTR).

---

## 🌐 Cloud Deployment (Render.com)

HalalLens AI includes a production `Dockerfile` and `render.yaml` configured to deploy seamlessly to **Render Web Services**:

1. **Push to GitHub**:
   ```bash
   git add .
   git commit -m "Prepare HalalLens AI for Render deployment"
   git remote add origin https://github.com/<your-username>/HalalLens.git
   git push -u origin main
   ```
2. **Deploy on Render**:
   - Go to [dashboard.render.com](https://dashboard.render.com/) -> **New +** -> **Web Service**.
   - Connect your GitHub repository.
   - Render will detect the `Dockerfile` automatically (or choose **Runtime: Docker**).
   - In **Environment Variables**, add:
     - `GEMINI_API_KEY`: Your Google Gemini API key.
     - `GEMINI_MODEL`: `gemini-3.8-flash` (or `gemini-3-flash-preview`).
   - Click **Deploy Web Service**.


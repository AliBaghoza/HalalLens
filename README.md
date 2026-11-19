# HalalLens AI (عدسة الحلال الذكية)

**HalalLens AI** is an AI Engineering platform designed to verify the Halal compliance of food products by analyzing ingredient label images through a backend-orchestrated Multi-Agent AI architecture.

---

## 📌 Project Overview

HalalLens AI focuses on solving the challenge of food ingredient verification using a collaborative Multi-Agent AI system where specialized agents cooperate through a real server-side data pipeline:

1. **Ingredient Vision Agent (Agent 1)**: Ingests the raw product image via Gemini Vision API (`gemini-3.8-flash` with resilient fallback), assesses source quality (`good`, `fair`, `poor`, `unreadable`), and extracts individual ingredients into a structured JSON contract (`original_name`, `normalized_name`, `confidence`) preserving packaging wording and uncertainty without making dietary rulings.
2. **Halal Verification Agent (Agent 2)**: Ingests the structured ingredient JSON directly from Agent 1, applies Islamic dietary jurisprudence (Fiqh al-At'imah) and food additive science, and generates a canonical assessment report with overall and per-ingredient rulings.
3. **Real Agent 1 → Agent 2 Backend Handoff**: Fully orchestrated server-side in PHP memory without client browser intermediary steps.
4. **Bilingual Arabic-First Interface**: Full RTL-first layout with English LTR secondary switching, using the universal **Tajawal** typography.
5. **Nearby Halal Restaurant Finder (Future Feature / Coming Soon)**: A planned extension designed for a future release to assist consumers in finding verified Halal dining options nearby.

---

## 🏗️ Technology Stack

### Currently Used
- **Backend**: PHP 8.2+ (Shared hosting, cPanel, and Docker compatible)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (Zero build tools, no Node.js, React, npm, or Vite)
- **Typography**: `Tajawal, Arial, sans-serif` universally applied as the visual brand identity
- **Networking**: PHP cURL with SSL verification, output buffering, and execution timeouts
- **AI Processing**: Google Gemini API via server-side REST integration (Multimodal Vision & Dietary Reasoning)
- **Orchestration**: Server-side Multi-Agent pipeline orchestration in `api/analyze.php`
- **Cloud Deployment**: Production `Dockerfile` and `render.yaml` for Render Web Services

### Planned Future Technologies (For Upcoming Extensions)
- **Geospatial & Mapping**: OpenStreetMap (OSM)
- **Amenity Querying**: Overpass API
- **Location Geocoding**: Nominatim Geocoding API
- **Client Geolocation**: Browser Geolocation API

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
│   └── verify.php            # Modular test endpoint for direct ingredient verification
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
├── Dockerfile                # Production container specification for Render deployment
├── .dockerignore             # Excludes local secrets and temporary files
├── render.yaml               # Render Blueprint deployment definition
├── README.md                 # Project architecture & documentation
├── AGENTS.md                 # Detailed Multi-Agent collaboration specification
└── .gitignore                # Protects config.php, .env, and uploaded files
```

---

## 🔄 Current Active Architecture

The active Multi-Agent pipeline executes entirely within the server backend:

```
[User Uploads Label Image]
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

## 🔮 Future Feature — Halal Restaurant Finder

HalalLens AI is planned to receive a future restaurant-discovery feature to help users discover nearby Halal dining options.

The planned feature may eventually utilize:
- OpenStreetMap (OSM) spatial datasets
- Overpass API for querying Halal dietary amenities and cuisine tags
- Nominatim Geocoding for city- and neighborhood-based search
- Location-based GPS search and interactive map discovery

```
[Future Planned Extension]
         │
         ▼
┌────────────────────────────────────────────────────────┐
│        Halal Restaurant Finder (Coming Soon)           │
│     Planned OpenStreetMap / Overpass / Nominatim       │
└────────────────────────────────────────────────────────┘
```

> [!IMPORTANT]
> **The Halal Restaurant Finder is not part of the currently released core functionality. It is presented in the current interface as a Coming Soon feature and is planned for a future release.**
> 
> It is not currently active, does not search live restaurant data in the current release, and is not included in the active test scope.

---

## 🏷️ Canonical Halal Terminology

HalalLens AI enforces four canonical uppercase statuses internally, localized into Arabic and English:

| Internal Canonical Status | Arabic Translation | English Translation | Criteria |
| :--- | :--- | :--- | :--- |
| `HALAL` | **حلال** | Halal | Strictly plant-based, aquatic, microbial, or certified Halal slaughtered animals. |
| `HARAM` | **حرام** | Haram | Contains pork, porcine gelatin, non-dhabihah land animals, blood, or intoxicants. |
| `DOUBTFUL` | **مشتبه فيه** | Doubtful | Questionable emulsifiers (e.g. E471), gelatin with unspecified source, enzymes. |
| `UNKNOWN` | **غير معروف** | Unknown | Illegible, corrupted label, or insufficient ingredient data. |

---

## 🔒 Security & Configuration Guidelines

### Server-Side Configuration

1. **Environment Variables (Recommended for Production & Render)**:
   Set the following variables in your server environment:
   ```bash
   export GEMINI_API_KEY="your-gemini-api-key"
   export GEMINI_MODEL="gemini-3.8-flash"
   ```

2. **File Fallback (`config/config.php`)**:
   On shared hosting or local development where environment variables are not easily set:
   - Copy `config/config.example.php` to `config/config.php`:
     ```bash
     cp config/config.example.php config/config.php
     ```
   - Edit `config/config.php` and set your credentials.
   - `config/config.php` is strictly excluded from version control via `.gitignore`.

### Upload Security

The `uploads/` directory includes an `.htaccess` file prohibiting script execution (`php_flag engine off`, denying `.php`, `.phtml`, `.cgi`, etc.) to prevent arbitrary code execution on shared hosting.

---

## 🧪 Testing & Verification

The test suite validates the active production functionality:

- **Image Upload & Validation**: Validates MIME types (JPEG, PNG, WebP) and 5MB size limit.
- **Ingredient Vision Agent (Agent 1)**: Multimodal OCR text extraction, quality rating (`good`, `fair`, `poor`, `unreadable`), uncertainty preservation.
- **Structured JSON Extraction**: Validates schema compliance (`original_name`, `normalized_name`, `confidence`).
- **Halal Verification Agent (Agent 2)**: Rule-based and jurisprudential analysis, canonical status assignment, individual ingredient assessments.
- **Real Agent 1 → Agent 2 Backend Handoff**: Verified direct in-memory passing in `api/analyze.php`.
- **Bilingual Interface**: Arabic RTL default and English LTR mode with Tajawal typography.
- **Error Handling & Resilience**: Execution timeout protection (120s), output buffering, automated model fallback on demand spikes.
- **API Security**: Complete isolation of credentials (zero key leakage).
- **Nearby Halal Restaurant Finder**: *Coming Soon — not included in the current functional test scope.*

---

## ⚠️ Known Limitations

- **Image Quality Dependency**: AI extraction accuracy depends directly on packaging image resolution, lighting, and label clarity.
- **Ambiguous Additives**: Some ingredients and additives (e.g., E471 mono- and diglycerides, unspecified gelatin) remain classified as `DOUBTFUL` when their plant vs. animal origin is not explicitly stated on the packaging.
- **Advisory Nature**: The platform provides automated nutritional and Islamic dietary analysis assistance; it is not an official Fatwa authority or Halal certification board.
- **Restaurant Finder**: The Halal Restaurant Finder is currently a planned future feature and is not available in the current release.

---

## 🚀 Local Development & Verification

To run HalalLens AI using PHP's built-in development server:

```powershell
# Start PHP built-in server from HalalLens root
php -S 127.0.0.1:8090 -t .
```

Open your browser at `http://127.0.0.1:8090`. The application will open in **Arabic (RTL)** with the **Tajawal** font. Click the language switcher in the header to switch to English (LTR).

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



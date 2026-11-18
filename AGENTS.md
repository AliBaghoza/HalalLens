# Multi-Agent Architecture Specification - HalalLens AI

This document outlines the collaborative Multi-Agent AI architecture powering **HalalLens AI**.

---

## 1. Architectural Philosophy

HalalLens AI divides the complex challenge of dietary verification into specialized, decoupled agents. Rather than asking a single generative model to simultaneously inspect an image, extract text, research Islamic jurisprudence, and format a verdict, HalalLens AI employs a pipelined Multi-Agent system orchestrated entirely on the backend.

### Why Backend Orchestration?

The frontend client does **NOT** orchestrate the Agent 1 → Agent 2 handoff:

1. **Security & Data Integrity**: Client browsers cannot manipulate, alter, or forge the intermediate structured ingredient JSON between Agent 1 and Agent 2.
2. **Network Efficiency**: Running the handoff inside the server eliminates an extra network round-trip between the user's mobile device and the server.
3. **Atomicity**: The analysis either completes in its entirety or provides a clean diagnostic error state.
4. **Decoupled Client**: The frontend simply issues one multipart request to `api/analyze.php` and receives the complete synthesized pipeline output.

---

## 2. Multi-Agent Collaboration Pipeline

```
┌────────────────────────────────────────────────────────┐
│                      Client User                       │
│             Uploads Product Label Image                │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼ HTTP POST multipart/form-data
┌────────────────────────────────────────────────────────┐
│                   api/analyze.php                      │
│             (Backend Agent Orchestrator)               │
│                                                        │
│  1. Validates file size, MIME type & magic bytes       │
│  2. Saves sanitized file to uploads/                   │
│  3. Instantiates Agent 1                               │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼
┌────────────────────────────────────────────────────────┐
│           Agent 1: Ingredient Vision Agent             │
│              (agents/IngredientAgent.php)              │
│                                                        │
│  • Reads image binary from secure disk storage         │
│  • Converts to Base64 inlineData payload               │
│  • Sends request to Gemini Vision API                  │
│  • Enforces JSON schema output                         │
│  • Extracts verified list of ingredients & E-numbers   │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼ Structured Ingredient JSON
┌────────────────────────────────────────────────────────┐
│                   api/analyze.php                      │
│        (Direct internal PHP memory handoff)            │
│                 Instantiates Agent 2                   │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼
┌────────────────────────────────────────────────────────┐
│           Agent 2: Halal Verification Agent            │
│                 (agents/HalalAgent.php)                │
│                                                        │
│  • Ingests structured ingredient array                 │
│  • Queries Islamic dietary rulings & additive database │
│  • Enforces Canonical Statuses:                        │
│    HALAL | HARAM | DOUBTFUL | UNKNOWN                  │
│  • Identifies flagged items & E-numbers                │
│  • Formulates bilingual reasoning (Arabic & English)   │
└────────────────────────┬───────────────────────────────┘
                         │
                         ▼ Final Combined JSON
┌────────────────────────────────────────────────────────┐
│                      Client User                       │
│    Renders Live Pipeline Tracker & Detailed Report     │
└────────────────────────────────────────────────────────┘
```

---

## 3. Agent 1: Ingredient Vision Agent

- **Class**: `HalalLens\Agents\IngredientAgent`
- **Source File**: `agents/IngredientAgent.php`
- **Model**: Configurable via `GEMINI_MODEL` (default: `gemini-3.8-flash`)
- **Primary Function**: Optical character recognition (OCR) and lexical extraction of food ingredient lists from images.

### Input Contract:
- `imagePath`: Absolute file system path to the validated image.

### Output JSON Contract:
```json
{
  "agent": "IngredientVisionAgent",
  "model": "gemini-3.8-flash",
  "success": true,
  "source_quality": "good",
  "source_text": "Wheat flour, palm oil, salt, emulsifier (E471), flavor enhancer (E621), natural color (E160a).",
  "ingredients": [
    {
      "original_name": "Wheat flour",
      "normalized_name": "Wheat flour",
      "confidence": 0.98
    },
    {
      "original_name": "palm oil",
      "normalized_name": "Palm oil",
      "confidence": 0.95
    },
    {
      "original_name": "salt",
      "normalized_name": "Salt",
      "confidence": 0.99
    },
    {
      "original_name": "emulsifier (E471)",
      "normalized_name": "E471",
      "confidence": 0.92
    },
    {
      "original_name": "flavor enhancer (E621)",
      "normalized_name": "E621",
      "confidence": 0.90
    },
    {
      "original_name": "natural color (E160a)",
      "normalized_name": "E160a",
      "confidence": 0.88
    }
  ],
  "ingredient_count": 6,
  "notes": [
    "Clear packaging label with English text."
  ]
}
```

---

## 4. Agent 2: Halal Verification Agent

- **Class**: `HalalLens\Agents\HalalAgent`
- **Source File**: `agents/HalalAgent.php`
- **Model**: Configurable via `GEMINI_MODEL` (default: `gemini-3.8-flash`)
- **Primary Function**: Evaluates each extracted ingredient and additive against Islamic dietary jurisprudence (Fiqh al-At'imah) and food science standards.

### Input Contract:
- `ingredients`: Array of strings containing extracted ingredient names and E-numbers.
- `preferredLang`: Target language code for analysis (`'ar'` or `'en'`).

### Canonical Status Taxonomy:
The agent must categorize products strictly into one of four canonical uppercase statuses:

1. **`HALAL`** (`حلال`):
   - Purely plant-based, marine, synthetic, microbial, or certified Halal slaughtered animals.
2. **`HARAM`** (`حرام`):
   - Contains pork, lard, bacon, porcine gelatin, non-dhabihah land animals, alcohol/intoxicants, or blood.
3. **`DOUBTFUL`** (`مشتبه فيه`):
   - Ambiguous emulsifiers (e.g., mono- and diglycerides E471), animal enzymes of unspecified origin, animal gelatin from unspecified animals, or disputed colorants (e.g., cochineal E120).
4. **`UNKNOWN`** (`غير معروف`):
   - Illegible labels, unidentifiable ingredients, or corrupted data.

### Output JSON Contract:
```json
{
  "agent": "HalalVerificationAgent",
  "model": "gemini-3.8-flash",
  "success": true,
  "status": "DOUBTFUL",
  "status_ar": "مشتبه فيه",
  "status_en": "Doubtful",
  "confidence": 0.88,
  "reasons": [
    "يحتوي المنتج على مستحلب E471 غير محدد المصدر، مما يجعله مشتبهاً فيه لحين التأكد من مصدره النباتي."
  ],
  "ingredient_assessments": [
    {
      "ingredient": "Wheat flour",
      "status": "HALAL",
      "status_ar": "حلال",
      "status_en": "Halal",
      "reason": "دقيق القمح مكون نباتي طاهر ومباح شرعاً."
    },
    {
      "ingredient": "E471",
      "status": "DOUBTFUL",
      "status_ar": "مشتبه فيه",
      "status_en": "Doubtful",
      "reason": "أحادي وثنائي الجليسريدات قد تستخلص من دهون حيوانية غير مذكاة أو مصادر نباتية."
    }
  ],
  "notes": [
    "يرجى التحقق من وجود علامة حلال معتمدة أو تصريح صريح بأن المستحلب نباتي 100%."
  ]
}
```

---

## 5. Modular Test Endpoint (`api/verify.php`)

To support unit testing, automated quality checks, and verification without requiring image re-upload, `api/verify.php` exposes a direct JSON endpoint:

- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "ingredients": ["flour", "sugar", "gelatin", "E471"],
    "lang": "ar"
  }
  ```
- **Response**: The complete Halal Verification Agent verdict.
- **Note**: This endpoint exists for testing and modular extensions; it is not called by the frontend during image verification.

---

## 6. Decoupled Restaurant Finder Service

The Halal Restaurant Finder (`api/restaurants.php` and `api/geocode.php`) is intentionally decoupled from the AI agent pipeline:
- **Architecture**: Microservice proxy querying OpenStreetMap Overpass and Nominatim APIs via server-side cURL.
- **Independence**: Does not interact with or depend upon the AI agents. Can be used independently by users looking for Halal dining in their immediate area.

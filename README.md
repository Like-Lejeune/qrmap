# QRMap — GPS stops at the door. QRMap starts there.

**Indoor navigation from a QR code, with an AI guide powered by Meta Muse Spark.**
Built in Yaoundé, Cameroon · Meta Global AI Developer Hackathon 2026 · Startup track · Theme: *Build for Your World*

🎥 Demo video: `<link>` · 🌐 [qrmap.app](https://qrmap.app)

---

## The problem

In Cameroon, hospitals, ministries, markets and trade fairs rarely have indoor signage visitors can trust. People ask around, wander and lose time — sometimes when it matters most, like in a hospital.

Classic indoor navigation needs beacons, a dedicated app and weeks of mapping. That does not scale to the buildings people actually use here.

QRMap needs only a QR code on a wall and a phone browser: **no app, no sign-up, no hardware.**

---

## What QRMap does

1. A visitor scans a QR code at the entrance or in a corridor.
2. The browser opens the building's 2D plan, with "You are here".
3. The visitor picks a destination, **or asks the AI guide in plain language** ("I have chest pain", "where do I pay?").
4. QRMap computes the route with A\* and guides turn by turn, with voice, across floors.

---

## Before the hackathon vs. built during the hackathon

To respect the "new work only" rule, the repository history is split: the first commit is the pre-existing QRMap product, and every later commit was made during the event.

| Existed before (first commit) | Built during the hackathon |
|---|---|
| QR scan → 2D plan in the browser | **AI guide** (Muse Spark, tool calling): free-text or voice questions, any language |
| A\* routing on the server, turn-by-turn steps | **Plan reading** (Muse Spark, image input + JSON schema output): a photo or PDF of a floor plan becomes rooms, corridors and doors |
| Plan editor with manual drawing | `RouteDescriber`: turns the A\* path into steps with visible landmarks, for the model to explain |
| Multi-floor sites, QR code generation | Doors as arrival points (the destination room stays a wall; the route ends at its door) |
| Laravel 12 · PHP 8.2 or latest· MySQL | Client-side A\* port in JavaScript (identical results to the PHP engine) + cached navigation bundle |
| | Health-check command `php artisan qrmap:ai-check` |

---

## How Meta's model is used

**Model:** `muse-spark-1.3` through the **Meta Model API** (`https://api.meta.ai/v1/chat/completions`).

| Where | What Muse Spark does | API features used |
|---|---|---|
| Visitor scan page — "Ask the guide" (`AiGuideController`, `AiGuide`) | Understands the visitor's need, picks a destination, calls the tool `calculer_itineraire(destination_id)`, then explains the real route with landmarks in the visitor's language | Tool calling, multi-turn conversation, `reasoning_effort: low` |
| Admin plan editor — "Lire un plan (IA)" (`PlanVisionController`, `PlanVision`) | Reads a photo or PDF of a floor plan and returns rooms, corridors, connectors and doors | Image input, `response_format` JSON schema, `reasoning_effort: medium` |

**Design rule: the model reads and talks, the algorithm computes.**

- The model **never draws a route**. Every route comes from QRMap's A\* engine through the tool call.
- Every destination ID chosen by the model is **checked on the server**; an invented ID is rejected and the model is asked again.
- Every plan returned by the model is **revalidated on the server**:
  - coordinates are clamped;
  - noise is removed;
  - doors are snapped to walls;
  - corridors are named so the router recognises them.

  The admin then reviews the result on top of the original photo.
- Medical rule in the guide's system prompt: for a medical emergency or a visitor feeling unwell, the guide routes to Emergency (or reception) right away, tells them to alert the nearest staff member, and never gives medical advice.

```
Visitor ──question──▶ Muse Spark ──tool call──▶ QRMap A* ──real route──▶ Muse Spark ──explanation──▶ Visitor
Admin   ──photo/PDF─▶ Muse Spark ──JSON plan──▶ server validation ──▶ editor (admin corrects) ──▶ saved plan
```

---

## Architecture

- **Backend:** Laravel 12, PHP 8.2, MySQL.
- **Frontend:** Blade views with vanilla JS and SVG. The visitor page works on any phone browser.
- **Coordinates:** percentages (0–100) of each floor, so the plans don't depend on any building size.
- **Routing:** A\* on a 100×100 grid, simplified with Douglas–Peucker.
  - Corridors, halls and waiting areas are walkable.
  - Rooms are walls except through their door.
  - Stairs and lifts link floors.

Main AI files:

```
app/Services/Ai/ModelClient.php        Meta Model API client (retries on 429, Meta-specific parameters)
app/Services/Ai/AiGuide.php            Tool-calling loop for the visitor guide
app/Services/Ai/RouteDescriber.php     A* path → steps with landmarks
app/Services/Ai/PlanVision.php         Plan-reading prompt, JSON schema and server-side normalisation
app/Http/Controllers/AiGuideController.php
app/Http/Controllers/PlanVisionController.php
app/Console/Commands/AiCheck.php       php artisan qrmap:ai-check
resources/views/partials/guide-ia.blade.php
```

---

## Run it locally

```bash
git clone <repo-url> && cd <repo>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Meta Model API settings in `.env`:

```
AI_PROVIDER=meta
AI_BASE_URL=https://api.meta.ai/v1
AI_API_KEY=your_key
AI_MODEL=muse-spark-1.3
AI_REASONING_EFFORT=low
AI_VISION_REASONING_EFFORT=medium
```

Check the connection:

```bash
php artisan qrmap:ai-check
php artisan qrmap:ai-check --image=path/to/floor-plan.jpg
```

**Demo data.** Import `hopital-demo.json`, a fictional two-floor hospital with 21 destinations and 5 QR codes, from the plan list with "Importer un plan". Then open a QR code's scan page and try:

- "I have chest pain"
- "My wife is about to give birth"
- "Where do I pay?"

For plan photos up to a few MB, set `post_max_size = 16M` in `php.ini`.

---

## What's next

- Field deployment in Yaoundé, starting with trade fairs and hospitals.
- Multi-floor import from a single architect PDF.
- More local languages for the voice guide.
- An accessibility mode with step-free routes, preferring lifts over stairs.

---

## Team

QRMap is a four-person team from Yaoundé, Cameroon.
Contact: contact@qrmap.app · WhatsApp +237 695 249 549
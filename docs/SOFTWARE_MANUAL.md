# SamEWS Software Manual
## Samburu Early Warning System — User and Technical Documentation
**Student:** Monicah Lekupe
**Supervisor:** Gideon Ofori Osabutey
**Institution:** Ashesi University
**Year:** 2026
**Live URL:** http://samews.42web.io
**GitHub:** https://github.com/monicah-pirisi/capstone

---

## Table of Contents

1. Introduction
2. System Overview
3. User Guide — All Pages
4. Risk Engine — Technical Documentation
5. Data Sources
6. Technology Stack
7. Limitations and Future Work

---

## 1. Introduction

SamEWS (Samburu Early Warning System) is a web-based drought early warning platform designed for Samburu County, Kenya. It integrates official scientific data from the National Drought Management Authority (NDMA) and Kenya Meteorological Department (KMD) with indigenous knowledge indicators from Samburu pastoralist communities, delivering actionable drought alerts through multiple communication channels.

The platform was developed as a capstone project at Ashesi University in response to the finding that formal early warning systems in Samburu County produce data but fail to translate warnings into protective action at the community level.

---

## 2. System Overview

### Purpose
To bridge the gap between drought data existing and communities actually acting on it, by:
- Computing a composite drought risk score using published scientific methodology
- Integrating indigenous knowledge as a cross-validation signal
- Routing stakeholder-specific recommended actions
- Providing ready-made alert messages for WhatsApp, radio, USSD, and social media

### Architecture
The system follows a three-layer architecture:

**Data Layer** — JSON flat files updated manually from official NDMA and KMD bulletins each month. No web scraping or automated API calls are used; data provenance is fully traceable to published government bulletins.

**Logic Layer** — PHP 8 backend classes. The RiskEngine class implements the Balint et al. (2013) Combined Drought Index. The DataRepository class loads JSON files. The Db class handles MySQL connections via PDO.

**Presentation Layer** — HTML5/CSS3/Vanilla JavaScript frontend. Chart.js v4.4.0 renders all data visualizations. No JavaScript frameworks are used.

---

## 3. User Guide — All Pages

### 3.1 Home Page (index.php)
**URL:** http://samews.42web.io

The homepage displays the current drought phase and risk score at a glance. It includes:
- Current alert level badge (Normal / Alert / Alarm / Emergency)
- Composite risk score (0–100)
- Quick navigation to all platform sections

No login is required. The page is accessible to all users.

---

### 3.2 Problem Page (problem.php)
**URL:** http://samews.42web.io/problem.php

Documents the research problem and background context:
- Evidence of EWS failure in Samburu County (2.4 million livestock deaths, 2021–2023)
- Literature review of barriers to EWS effectiveness
- Justification for the platform approach
- ACM-formatted citations to peer-reviewed sources

---

### 3.3 Solution Page (solution.php)
**URL:** http://samews.42web.io/solution.php

Explains the platform's design rationale:
- How the socio-technical approach addresses identified barriers
- Multi-channel dissemination strategy (radio, USSD, WhatsApp, web)
- Integration of scientific and indigenous knowledge systems

---

### 3.4 Current Alert Page (current-alert.php)
**URL:** http://samews.42web.io/current-alert.php

The core dashboard of the platform. Displays:

**Risk Score Gauge**
A circular gauge showing the composite score (0–100) colour-coded by phase:
- Green: Normal (≥80)
- Amber: Alert (60–79)
- Red: Alarm (40–59)
- Dark Red: Emergency (<40)

**Indicator Sub-Scores**
Three scored indicators (Balint et al. 2013 CDI):
- Rainfall — 50% weight — current mm vs long-term average
- Vegetation — 25% weight — VCI reading vs threshold of 35
- Temperature — 25% weight — KMD forecast max vs normal 30°C

**Indigenous Cross-Validation**
- Indigenous stress percentage — proportion of community indicators signalling drought
- Score adjustment — formula: (0.5 − ratio) × 10, capped at ±5 points

**Ground Conditions Panel**
Real NDMA/WFP data displayed as context (not scored):
- Livestock body condition (NDMA field classification)
- Distance to water source (NDMA bulletin, normal baseline 7 km)
- Food Consumption Score (WFP methodology, scale 0–42)

**Reasons Panel**
Auto-generated text explaining why the score is at its current level, with severity flags (high/medium).

**Recommended Actions**
Stakeholder-specific actions for: Government, NGOs, Radio Stations, Pastoralists, Intermediaries.

**Channel Messages Tab**
Ready-made alert messages for: WhatsApp, Facebook, 30-second radio script, 60-second radio script, USSD status screen.

**What-If Simulation**
GET parameters can override any scored input for scenario testing:
- Example: /current-alert.php?rainfall_mm=5&ndvi=10
- Useful for demonstrating what the score would be under drought conditions

**Data Sources**
Cards showing the NDMA and KMD source bulletins with links to official publications.

---

### 3.5 Prototype Page (prototype.php)
**URL:** http://samews.42web.io/prototype.php

The integration page — the core research contribution. Displays:

**Integrated Knowledge Comparison**
Three cards side by side:
1. Official Sources (KMD forecast + NDMA phase with sub-county vegetation breakdown)
2. Indigenous Indicators (community signals grouped by category: animals, weather, land, plants — Tier 1 General Elders and Tier 2 Specialist Elders)
3. Combined Interpretation (agreement level + confidence meter)

Agreement levels:
- Full Agreement (both stressed) → 90% confidence
- Partial Agreement (one stressed) → 55% confidence
- Disagreement (neither stressed) → 30% confidence

**Why Integration Matters**
Three evidence cards — Trust, Local Relevance, Better Decisions — each with a direct quote from research participants.

**How the Integration Works**
Five-step process diagram: Scientific Data → Indigenous Observations → Comparison Logic → Alert Generation → Dissemination.

**Supporting Modules**
Links to all platform components: Findings Dashboard, Current Alert Engine, Dissemination Channels, USSD Simulator, Stakeholder Profiles, Education Hub.

**Now vs Future Table**
Transparent comparison of current prototype capabilities versus what a production deployment would require.

**Technology Stack**
PHP 8+, MySQL, HTML/CSS/Vanilla JS.

---

### 3.6 Scientific Data Page (scientific-data.php)
**URL:** http://samews.42web.io/scientific-data.php

Visualizes five years of official KMD and NDMA bulletin data.

**Current Status Cards**
Four cards showing live NDMA data: Drought Phase, Food Security percentages, Livestock Condition, Distance to Water.

**Vegetation Condition by Sub-County**
Colour-coded breakdown for Samburu East (severe deficit), Samburu North (moderate deficit), Samburu Central (normal).

**NDMA 4-Month Trend Timeline**
Phase transition cards from November 2025 to February 2026 with key metrics: VCI, Rainfall mm, % of LTM, Food FCS, Water km.

**NDMA Trend Charts (4 charts)**
All built with Chart.js using data from ndma_history.json:
1. VCI Trend — line chart, red dashed threshold at 35
2. Rainfall % LTM — bar chart, colour-coded green/amber/red
3. Food Consumption Score — line chart, threshold lines at 35 (acceptable) and 21 (borderline)
4. Water Distance — line chart, dashed threshold at 7 km normal baseline

Each chart includes a decision signal panel explaining when to act.

**KMD Monthly Forecast History**
Timeline cards for recent KMD monthly forecasts with outlook category and Samburu-specific bulletin text.

**KMD Bulletin Data Charts (3 charts)**
All built with Chart.js using data from kmd_seasonal.json:
1. Tercile Probabilities — stacked bar (Above/Near/Below Normal), MAM 2021–MAM 2026
2. Rainfall Actuals % LTM — bar chart with 100% normal reference line
3. Drought SPI Probabilities — grouped bar with Alert (46%) and Alarm (16%) baseline reference lines

**Seasonal Forecast Table**
Full table of all KMD seasonal forecast entries 2021–2026 with rainfall outlook, temperature, and vegetation/pasture categories. Hover on cells to read the original bulletin source text.

---

### 3.7 Findings Page (findings.php)
**URL:** http://samews.42web.io/findings.php

Presents qualitative research findings from 12 semi-structured interviews:
- Seven emergent themes mapped to four research questions
- Seven socio-technical barrier cards with participant quotes as evidence
- Seven recommendations from Chapter 5 mapped to responsible actors
- STS framework interpretation

---

### 3.8 Indigenous Data Page (indigenous-data.php)
**URL:** http://samews.42web.io/indigenous-data.php

Reference page for all indigenous drought indicators documented in field research:
- Organised by category: Animal Behaviour, Weather and Sky, Land and Temperature, Vegetation and Plants
- Two knowledge tiers: Tier 1 General Elders (used in scoring), Tier 2 Specialist Elders (displayed only)
- Current status of each indicator (stressed / normal)
- Source attribution to field research chapter

---

### 3.9 Channels Page (channels.php)
**URL:** http://samews.42web.io/channels.php

Dissemination toolkit with pre-formatted templates for all channels:
- WhatsApp messages (five alert phase variants)
- Facebook/X posts
- Radio scripts — 30-second and 60-second with sound effect cues
- USSD menu content and status screens

All templates are auto-filled from the current risk level via the API.

---

### 3.10 USSD Simulator (ussd-simulator.php)
**URL:** http://samews.42web.io/ussd-simulator.php

Interactive phone simulator for the USSD service (*384#):
- Realistic feature phone UI with keypad
- Live risk data fetched from the API
- Menu options: Current Alert Level, Advice for Pastoralists, Where to Get Help, Change Language
- Bilingual support: English and Samburu language
- Emergency contacts screen

Designed for users with basic phones and no internet access.

---

### 3.11 Radio Scripts Page (radio-scripts.php)
**URL:** http://samews.42web.io/radio-scripts.php

Pre-written broadcast scripts for community radio stations:
- 30-second scripts for each alert phase
- 60-second scripts for each alert phase
- Sound effect cue instructions included
- Ready for immediate broadcast when an alert is issued

---

### 3.12 WhatsApp Templates Page (whatsapp-templates.php)
**URL:** http://samews.42web.io/whatsapp-templates.php

WhatsApp message templates for community group broadcasts:
- One template per alert phase (Normal through Emergency)
- Formatted for readability on mobile screens
- Copy button for each template

---

### 3.13 Stakeholders Page (stakeholders.php)
**URL:** http://samews.42web.io/stakeholders.php

Profiles for five stakeholder groups:
- Government (NDMA, County Government, National Government)
- NGOs (humanitarian and development organisations)
- Radio Stations (community and national radio)
- Pastoralists (herders and community members)
- Intermediaries (chiefs, ward administrators, community scouts)

Each profile shows: members and entities, preferred communication channels, per-phase response actions.

---

### 3.14 Resources Page (resources.php)
**URL:** http://samews.42web.io/resources.php

Education hub for users wanting to understand the system:
- Five-phase colour-coded drought guide with descriptions
- Risk score methodology explained in plain language
- Indigenous indicator reference with categories
- External resource links: NDMA, KMD, FEWS NET, WFP

---

### 3.15 Admin Panel (admin.php)
**URL:** http://samews.42web.io/admin.php
**Password:** admin123

Password-protected dashboard for:
- Viewing contact form submissions stored in MySQL
- Viewing feedback entries
- Basic site management

To change the password: run `php -r "echo password_hash('newpassword', PASSWORD_BCRYPT);"` and update `ADMIN_PASSWORD_HASH` in config.php.

---

## 4. Risk Engine — Technical Documentation

### 4.1 Scoring Methodology
The composite drought risk score is computed by the `RiskEngine::assess()` static method in `includes/RiskEngine.php`.

**Methodology:** Simple Additive Weighting (Weighted Arithmetic Mean) — a standard Multi-Criteria Decision Making (MCDM) technique.

**Weights:** Balint et al. (2013) Combined Drought Index for Kenya ASALs — the only peer-reviewed study providing empirically derived weights for a Kenya ASAL composite drought index.

| Indicator | Weight | Data Source | Normalisation |
|---|---|---|---|
| Rainfall | 50% | NDMA bulletin (current_mm / long_term_avg_mm) | Bounded ratio: 0–100 |
| Vegetation (VCI) | 25% | NDMA bulletin (VCI reading, threshold = 35) | Bounded ratio: 0–100 |
| Temperature | 25% | KMD bulletin (max_celsius, normal = 30°C, extreme = 40°C) | Inverted linear decay |

**Reference:** Balint, Z., Mutua, F., Muchiri, P., and Omuto, C. T. (2013). Monitoring Drought with the Combined Drought Index in Kenya. In: Paron, P., Omuto, C. T., and Oroda, A. (eds.), *Developments in Earth Surface Processes*, Vol. 16, Chapter 23, pp. 341–356. Elsevier. DOI: 10.1016/B978-0-444-59559-1.00023-2.

### 4.2 Normalisation Formulas

**Rainfall (Bounded Ratio):**
```
score = min(100, (current_mm / avg_mm) × 100)
score = 100 if current_mm ≥ avg_mm
score = 0   if current_mm ≤ 0
```

**Vegetation/VCI (Bounded Ratio):**
```
score = min(100, (ndvi / ndvi_normal) × 100)
score = 100 if ndvi ≥ ndvi_normal (35)
score = 0   if ndvi ≤ 0.1
```

**Temperature (Inverted Linear Decay):**
```
score = (1 − ((temp − normal) / (extreme − normal))) × 100
score = 100 if temp ≤ 30°C (normal)
score = 0   if temp ≥ 40°C (extreme)
```
Higher temperature = lower score because higher temperature increases evapotranspiration and moisture loss.

### 4.3 Composite Score
```
composite = (rainfall × 0.50) + (vegetation × 0.25) + (temperature × 0.25)
```

### 4.4 Indigenous Cross-Validation Adjustment
Indigenous knowledge is not assigned a weight (no published study provides one). Instead the raw community stress ratio (0.0–1.0) adjusts the composite by up to ±5 points:
```
adjustment = (0.5 − ratio) × 10    [capped at ±5]

ratio = 1.0  →  −5 pts  (community signals maximum drought stress)
ratio = 0.5  →   0 pts  (community neutral)
ratio = 0.0  →  +5 pts  (community signals no stress)
```

**Basis:** Derbyshire et al. (2024) and Radeny et al. (2019) document that indigenous indicators in northern Kenya detect drought stress earlier than satellite data, justifying their role as a directional cross-check.

### 4.5 Phase Classification
```
Score ≥ 80  →  Normal
Score ≥ 60  →  Alert
Score ≥ 40  →  Alarm
Score < 40  →  Emergency
```

### 4.6 Indigenous Stress Ratio Calculation
The stress ratio is computed by keyword scanning of indicator status fields in `indigenous_indicators.json`. Each general-tier indicator is checked against a list of stress keywords (deteriorating, low, sparse, drying, unusual, restless, below, poor, declining, drought, stress, browning, early movement, dry-season, above normal, rapidly). Specialist-tier indicators are excluded to avoid misclassification of celestial and spiritual readings.

```
stress_ratio = stressed_count / total_general_indicators
```

---

## 5. Data Sources

| Source | Data Provided | Update Frequency | File |
|---|---|---|---|
| NDMA Samburu County Drought Bulletin | VCI, rainfall mm, livestock, water distance, FCS, phase | Monthly | ndma_latest.json |
| KMD Monthly Forecast Bulletin | Max/min temperature, rainfall outlook, advisory | Monthly | kmd_summary.json |
| KMD Seasonal Climate Outlook | Tercile probabilities, seasonal outlook 2021–2026 | Seasonal | kmd_seasonal.json |
| KMD Monthly Bulletins (History) | Monthly forecast history | Monthly | kmd_bulletins.json |
| NDMA 4-Month Trend | Ground conditions Nov 2025–Feb 2026 | Manual | ndma_history.json |
| Field Research (2026) | Indigenous indicators, statuses, tiers | Static | indigenous_indicators.json |
| Channel Content | Alert message templates per phase | Static | channels_content.json |

**Data Update Process:**
1. Download the latest NDMA Samburu County Drought Bulletin (ndma.go.ke)
2. Download the latest KMD Monthly Forecast Bulletin (meteo.go.ke)
3. Update the corresponding JSON files with the new values
4. Save and re-upload the JSON files to the server via FileZilla

---

## 6. Technology Stack

| Component | Technology | Purpose |
|---|---|---|
| Backend | PHP 8+ | Server-side logic, risk engine, API endpoints |
| Database | MySQL | Contact submissions, feedback, bulletin links |
| Database Access | PDO (PHP Data Objects) | Prepared statements, injection prevention |
| Frontend | HTML5 / CSS3 | Structure and styling |
| JavaScript | Vanilla JS (ES6) | API calls, chart rendering, UI interactions |
| Charts | Chart.js v4.4.0 | All data visualizations |
| Local Server | XAMPP (Apache + MySQL) | Local development environment |
| Live Hosting | InfinityFree | Free PHP/MySQL shared hosting |
| Version Control | Git / GitHub | Source code management |

---

## 7. Limitations and Future Work

### Current Limitations

**Static JSON data layer**
Data is updated manually each month by reading official NDMA and KMD bulletins and editing JSON files. An automated scraper or API connection would make the platform truly real-time.

**Indigenous keyword matching**
The stress ratio is computed by keyword scanning of text fields. This is a simplified binary classifier that may miss nuanced indicator statuses. A mobile reporting app allowing community scouts to submit structured data would be more reliable.

**No real USSD service**
The USSD simulator is a web-based demo. A real USSD service would require a telco gateway integration (e.g. Africa's Talking or Safaricom) and a registered shortcode.

**No SMS/WhatsApp automation**
Message templates are generated but sending is manual. Automated broadcast would require the Africa's Talking SMS API or WhatsApp Business API.

**Capstone-grade authentication**
The admin panel uses a single bcrypt-hashed password. A production system would require role-based access control with user accounts for NDMA staff, county government, NGOs, and radio stations.

**English-only interface**
The web interface is in English. A Samburu and Kiswahili interface would significantly improve accessibility for pastoralist communities.

**Free hosting constraints**
InfinityFree imposes CPU limits and does not support cron jobs, so automated data syncing is not possible on the current hosting plan.

### Recommended Future Work

1. Automate NDMA and KMD data ingestion via scheduled scripts or web scrapers
2. Build a community scout mobile app for structured indigenous indicator reporting
3. Integrate Africa's Talking API for real SMS and USSD delivery
4. Translate the full interface into Samburu and Kiswahili
5. Implement role-based access control for multi-stakeholder use
6. Conduct field validation with Samburu pastoralist communities
7. Establish a joint warning validation committee (elders, chiefs, government) to review alerts before dissemination
8. Deploy on a cloud VM (e.g. AWS, DigitalOcean) with SSL, automated backups, and 99.9% uptime SLA

---

*End of Software Manual*
*SamEWS — Samburu Early Warning System | Ashesi University Capstone 2026*

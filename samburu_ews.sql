-- SamburuEWS normalized database schema
-- MySQL 8+ 
-- Engine: InnoDB, Charset: utf8mb4

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ----------------------------
-- 1) Core dimensions
-- ----------------------------

CREATE TABLE locations (
  location_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  admin_level ENUM('county','subcounty','ward','village') NOT NULL,
  parent_location_id INT NULL,
  latitude DECIMAL(9,6) NULL,
  longitude DECIMAL(9,6) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_location (name, admin_level, parent_location_id),
  CONSTRAINT fk_location_parent
    FOREIGN KEY (parent_location_id) REFERENCES locations(location_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE data_sources (
  source_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,  -- e.g., "KMD", "NDMA", "Community Reporter"
  source_type ENUM('government','ngo','community','research','media','other') NOT NULL,
  url VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_source (name, source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 2) KMD monthly forecast links (auto-synced pointer)
-- ----------------------------

CREATE TABLE kmd_monthly_forecasts (
  kmd_monthly_forecast_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NULL,                 -- NULL if national/general
  forecast_month DATE NOT NULL,         -- use first day of month (YYYY-MM-01)
  title VARCHAR(255) NULL,
  page_url VARCHAR(500) NOT NULL,       -- KMD page
  pdf_url VARCHAR(500) NULL,            -- direct PDF if available
  issue_date DATE NULL,
  synced_at DATETIME NOT NULL,
  source_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_kmd_month_loc (forecast_month, location_id),
  INDEX idx_kmd_synced_at (synced_at),
  CONSTRAINT fk_kmd_month_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_kmd_month_source
    FOREIGN KEY (source_id) REFERENCES data_sources(source_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 3) Scientific indicators (normalized)
-- ----------------------------

CREATE TABLE indicator_types (
  indicator_type_id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,     -- e.g., rainfall_pct_normal, vci_3month, water_pans_pct
  name VARCHAR(150) NOT NULL,
  unit VARCHAR(30) NULL,               -- %, index, mm, etc.
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE indicator_observations (
  indicator_observation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  indicator_type_id INT NOT NULL,
  location_id INT NOT NULL,
  obs_date DATE NOT NULL,
  value DECIMAL(12,4) NOT NULL,
  source_id INT NULL,
  source_url VARCHAR(500) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_indicator_loc_date (indicator_type_id, location_id, obs_date),
  INDEX idx_indicator_loc_date (location_id, obs_date),
  INDEX idx_indicator_type_date (indicator_type_id, obs_date),

  CONSTRAINT fk_ind_obs_type
    FOREIGN KEY (indicator_type_id) REFERENCES indicator_types(indicator_type_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ind_obs_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ind_obs_source
    FOREIGN KEY (source_id) REFERENCES data_sources(source_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: drought phase catalogue + assessments
CREATE TABLE drought_phases (
  drought_phase_id INT AUTO_INCREMENT PRIMARY KEY,
  code ENUM('NORMAL','WATCH','ALERT','ALARM','EMERGENCY') NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE drought_phase_assessments (
  drought_phase_assessment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NOT NULL,
  assessment_date DATE NOT NULL,
  drought_phase_id INT NOT NULL,
  source_id INT NULL,
  source_url VARCHAR(500) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_phase_loc_date (location_id, assessment_date),
  INDEX idx_phase_date (assessment_date),

  CONSTRAINT fk_phase_assess_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_phase_assess_phase
    FOREIGN KEY (drought_phase_id) REFERENCES drought_phases(drought_phase_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_phase_assess_source
    FOREIGN KEY (source_id) REFERENCES data_sources(source_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 4) Indigenous indicators (normalized)
-- ----------------------------

CREATE TABLE indigenous_indicator_types (
  indigenous_indicator_type_id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,     -- e.g., bird_migration, unusual_winds
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80) NULL,            -- animals/plants/weather/social
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE indigenous_observations (
  indigenous_observation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  indigenous_indicator_type_id INT NOT NULL,
  location_id INT NOT NULL,
  obs_date DATE NOT NULL,

  -- normalized qualitative fields
  intensity ENUM('low','medium','high') NULL,
  confidence ENUM('low','medium','high') NULL,
  observer_role ENUM('pastoralist','elder','youth','intermediary','extension','other') NULL,

  source_id INT NULL,
  source_url VARCHAR(500) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_ind_loc_date (location_id, obs_date),
  INDEX idx_ind_type_date (indigenous_indicator_type_id, obs_date),

  CONSTRAINT fk_indigenous_obs_type
    FOREIGN KEY (indigenous_indicator_type_id) REFERENCES indigenous_indicator_types(indigenous_indicator_type_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_indigenous_obs_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_indigenous_obs_source
    FOREIGN KEY (source_id) REFERENCES data_sources(source_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 5) Risk models + computed risk assessments
-- ----------------------------

CREATE TABLE risk_models (
  risk_model_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  version VARCHAR(20) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_model (name, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE risk_assessments (
  risk_assessment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NOT NULL,
  assessed_at DATETIME NOT NULL,
  risk_model_id INT NOT NULL,
  score INT NOT NULL,
  risk_level ENUM('LOW','MODERATE','HIGH') NOT NULL,
  explanation TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_risk_loc_time (location_id, assessed_at),

  CONSTRAINT fk_risk_assess_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_risk_assess_model
    FOREIGN KEY (risk_model_id) REFERENCES risk_models(risk_model_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 6) Stakeholders + channels + preferences (routing/dissemination)
-- ----------------------------

CREATE TABLE stakeholder_types (
  stakeholder_type_id INT AUTO_INCREMENT PRIMARY KEY,
  code ENUM('government','ngo','radio','pastoralist','intermediary') NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stakeholders (
  stakeholder_id INT AUTO_INCREMENT PRIMARY KEY,
  stakeholder_type_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  location_id INT NULL,
  contact_phone VARCHAR(30) NULL,
  contact_email VARCHAR(190) NULL,
  contact_whatsapp VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_stakeholder_type (stakeholder_type_id),

  CONSTRAINT fk_stake_type
    FOREIGN KEY (stakeholder_type_id) REFERENCES stakeholder_types(stakeholder_type_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_stake_loc
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dissemination_channels (
  channel_id INT AUTO_INCREMENT PRIMARY KEY,
  code ENUM('radio','social_media','ussd','sms','baraza','web') NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stakeholder_channel_preferences (
  stakeholder_id INT NOT NULL,
  channel_id INT NOT NULL,
  priority TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (stakeholder_id, channel_id),
  CONSTRAINT fk_scp_stakeholder
    FOREIGN KEY (stakeholder_id) REFERENCES stakeholders(stakeholder_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_scp_channel
    FOREIGN KEY (channel_id) REFERENCES dissemination_channels(channel_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 7) Website contact messages
-- ----------------------------

CREATE TABLE contact_messages (
  contact_message_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  stakeholder_type_label VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contact_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 8) Ingestion / sync run logs
-- ----------------------------

CREATE TABLE ingestion_runs (
  ingestion_run_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  job_name VARCHAR(80) NOT NULL,         -- e.g., "sync_kmd_monthly"
  status ENUM('success','fail') NOT NULL,
  message TEXT NULL,
  ran_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ingestion_job_time (job_name, ran_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 9) Hybrid integration: auto-synced bulletin links
--    Populated daily by scripts/sync_official_reports.php
--    KMD  rows = Scientific Forecast (future outlook)
--    NDMA rows = Drought Situation   (current phase)
-- ----------------------------

CREATE TABLE kmd_ndma_reports (
  id          BIGINT       AUTO_INCREMENT PRIMARY KEY,
  source_org  ENUM('KMD','NDMA') NOT NULL,   -- KMD=forecast  NDMA=situation
  report_type VARCHAR(80)  NOT NULL,          -- e.g. 'monthly_forecast', 'national_drought_bulletin'
  title       VARCHAR(500) NULL,              -- link text extracted from the page
  page_url    VARCHAR(1000) NOT NULL,         -- listing page where link was found
  pdf_url     VARCHAR(1000) NULL,             -- direct PDF if scraper found one
  synced_at   DATETIME     NOT NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reports_source_synced (source_org, synced_at DESC),
  INDEX idx_reports_type          (report_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- 10) Official summaries: one manually maintained row per org
--     Admin updates these via admin.php after reading each bulletin.
--     KMD  row: outlook_category (feeds risk engine rainfall forecast input)
--     NDMA row: drought_phase    (feeds risk engine current situation input)
-- ----------------------------

CREATE TABLE official_summaries (
  id                INT          AUTO_INCREMENT PRIMARY KEY,
  source_org        ENUM('KMD','NDMA') NOT NULL UNIQUE,  -- one row per org
  outlook_category  ENUM('below_average','near_average','above_average') NULL,  -- KMD only
  drought_phase     ENUM('NORMAL','WATCH','ALERT','ALARM','EMERGENCY')   NULL,  -- NDMA only
  summary_text      TEXT         NULL,
  valid_period      VARCHAR(100) NULL,   -- e.g. "March-May 2025" or "February 2025"
  updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Seed reference rows
-- ----------------------------

-- Data sources
-- KMD and NDMA are the two formal EWS organisations operating in Samburu (Chapters 4–5).
-- Kenya Red Cross and Samburu County Government are additional institutional actors
-- identified in §4.6.1 as key but inconsistently coordinated stakeholders.
-- Community Elders are the primary trusted knowledge source in pastoralist communities (§4.2, §4.3).
INSERT INTO data_sources (name, source_type, url) VALUES
('Kenya Meteorological Department (KMD)',          'government', 'https://meteo.go.ke'),
('National Drought Management Authority (NDMA)',   'government', 'https://ndma.go.ke'),
('Kenya Red Cross – Samburu County',               'ngo',        'https://www.redcross.or.ke'),
('Samburu County Government',                      'government', 'https://www.samburu.go.ke'),
('Samburu Community Elders',                       'community',  NULL);

-- Drought phase descriptions
-- Codes match the NDMA drought phase classification used in Samburu field assessments.
INSERT INTO drought_phases (code, description) VALUES
('NORMAL',    'Pasture, water, and livestock body condition are within normal seasonal range. No intervention required.'),
('WATCH',     'Early signs of stress detected: below-average rainfall, pasture beginning to decline. Enhanced monitoring advised.'),
('ALERT',     'Drought alert: pasture and water below normal, livestock body condition declining. Preparedness actions should begin.'),
('ALARM',     'Drought alarm: severe pasture and water deficit, livestock mortality rising. Emergency response activation required.'),
('EMERGENCY', 'Drought emergency: critical pasture and water failure, acute livestock and human food insecurity. Full humanitarian response needed.');

-- Stakeholder types
INSERT INTO stakeholder_types (code, description) VALUES
('government',   'Government/public agencies (NDMA, KMD, County Government, Chiefs)'),
('ngo',          'NGOs and humanitarian partners (Kenya Red Cross, development partners)'),
('radio',        'Radio stations broadcasting warnings in local languages'),
('pastoralist',  'Pastoralist community members and livestock keepers'),
('intermediary', 'Community intermediaries: elders, chiefs, religious leaders, women\'s groups');

-- Dissemination channels
-- Radio and baraza (community meeting) were identified as most effective for reaching
-- pastoralists in Samburu, including those without smartphones (§4.7.3, §5.1.4).
-- Voice messages in local languages (Samburu/Kiswahili) are the recommended format.
INSERT INTO dissemination_channels (code, description) VALUES
('radio',        'Local FM radio broadcasts in Samburu and Kiswahili – widest reach for remote pastoralists'),
('social_media', 'WhatsApp and Facebook – effective for town-based and connected users'),
('ussd',         'USSD pull menu (*xxx#) – accessible on basic phones without internet'),
('sms',          'SMS alerts – limited by literacy; recommended for literate contacts only'),
('baraza',       'Chief/community baraza and council of elders meetings – trusted, high-action channel'),
('web',          'Web dashboard – for institutional users, government officers, and NGO staff');

-- ----------------------------
-- Locations: Samburu County and sub-counties
-- Research finding §4.1.2: Warnings from KMD are county-level rather than sub-county-specific,
-- which reduces their practical value. Samburu County is divided into North, East, Central,
-- and West. North receives significantly less rainfall than West.
-- Coordinates are approximate centroids for each administrative unit.
-- ----------------------------
INSERT INTO locations (name, admin_level, parent_location_id, latitude, longitude) VALUES
('Samburu', 'county', NULL, 1.2167, 36.9500);

INSERT INTO locations (name, admin_level, parent_location_id, latitude, longitude) VALUES
('Samburu North',    'subcounty', 1, 1.7800, 36.7900),  -- drier; Baragoi area
('Samburu East',     'subcounty', 1, 0.6200, 37.5800),  -- Archer's Post / Wamba area
('Samburu Central',  'subcounty', 1, 1.0900, 36.7000),  -- Maralal area (county HQ)
('Samburu West',     'subcounty', 1, 1.3000, 36.4800);  -- Suguta Marmar area; higher rainfall

-- ----------------------------
-- Scientific indicator types
-- Derived from indicators referenced throughout Chapters 4–5 and the risk engine (RiskEngine.php).
-- ----------------------------
INSERT INTO indicator_types (code, name, unit, description) VALUES
('rainfall_pct_normal', 'Rainfall – Percentage of Long-Term Normal',    '%',
 'Observed rainfall as a percentage of the long-term seasonal average for the location. Below 75% signals drought risk. Primary KMD metric for Samburu seasonal forecasts.'),
('vci_3month',          'Vegetation Condition Index (3-month)',          'index (0–100)',
 'NDVI-derived index measuring vegetation health relative to historical range. Values below 35 indicate severe vegetation stress and are a key drought signal for pastoral areas.'),
('water_pans_pct',      'Water Pans – Percentage of Capacity',          '%',
 'Volume of water in community water pans as a percentage of full capacity. Critical pastoral livelihood indicator; below 30% triggers livestock movement decisions.'),
('uv_index',            'UV Index',                                      'index (0–11+)',
 'Daily maximum ultraviolet radiation index. Extreme UV (≥11) combined with below-average rainfall reinforces drought early warning signals via smartphone weather apps.');

-- ----------------------------
-- Indigenous indicator types
-- Extracted directly from §4.2.1 and Table 4.2 of the research findings.
-- These represent the multi-source indigenous forecasting system used by Samburu elders.
-- The system is tiered: ordinary elders use categories 'animals','plants','weather','land';
-- specialist elders use 'celestial' and 'spiritual' indicators (§4.2.1).
-- ----------------------------
INSERT INTO indigenous_indicator_types (code, name, category, description) VALUES
('animal_early_movement',
 'Animals Moving Earlier Than Usual',
 'animals',
 'Livestock and wildlife beginning seasonal migration earlier than the normal calendar. Observed by pastoralists as a strong signal that drought onset is imminent and pasture will fail sooner than expected.'),

('animal_weakness',
 'Animal Weakness and Body Condition Decline',
 'animals',
 'Progressive weakness, reduced energy, and declining body condition in cattle and other livestock. Indicates that pasture quality and water availability are already below adequate levels.'),

('cattle_seasonal_patterns',
 'Cattle Behavioural Patterns Indicating Rainy Season',
 'animals',
 'Specific behavioural cues in cattle – such as increased restlessness, altered grazing direction, or unusual vocalisation – monitored by elders to estimate when the rainy season is about to begin.'),

('cloud_patterns',
 'Cloud Movement and Formation Patterns',
 'weather',
 'Observation of cloud types, direction of movement, density, and colour. Elders distinguish between clouds that signal approaching rainfall and those associated with prolonged dry conditions.'),

('sky_appearance',
 'Sky Appearance and Colour at Dawn and Dusk',
 'weather',
 'The colour, clarity, and overall appearance of the sky, particularly at sunrise and sunset. Specific sky conditions are interpreted by elders as indicators of coming rain or continued drought.'),

('strong_dry_winds',
 'Strong Dry Winds',
 'weather',
 'Unusually strong winds with low humidity, particularly from specific directions. Identified by elders as a signal of delayed rainfall or incoming dry period.'),

('land_drying',
 'Rate of Land and Ground Surface Drying',
 'land',
 'How quickly the topsoil and ground surface dry out between rainfall events. Rapid drying relative to the season indicates soil moisture deficit and early drought stress.'),

('heat_intensity',
 'Unusual Heat Intensity',
 'land',
 'Abnormally high ambient temperatures and ground heat, assessed without instruments through community experience. Combined with other indicators, signals drought onset.'),

('grass_drying',
 'Drying and Browning of Grass',
 'plants',
 'Early or accelerated drying and browning of seasonal grasses before the expected end of the rains. Indicates pasture failure and is a primary trigger for livestock movement decisions.'),

('unusual_tree_changes',
 'Unusual or Early Changes in Trees and Shrubs',
 'plants',
 'Premature leaf drop, early flowering, or unusual changes in specific tree and shrub species that elders associate with coming dry conditions. Species vary by sub-county.'),

('celestial_signs',
 'Celestial and Star Positions (Specialist Elders)',
 'celestial',
 'Reading of star positions, planetary movements, and other celestial signs by specialist elders. This is an advanced tier of indigenous forecasting consulted when ordinary indicators are ambiguous. Documented in §4.2.1 as distinct from general elder knowledge.'),

('spiritual_readings',
 'Spiritual and Prophetic Forecasting (Divine Seers)',
 'spiritual',
 'Forecasting by recognised divine seers (a distinct specialist class) in the most remote Samburu communities. These seers are believed to foresee local climatic conditions and are the primary warning source for communities beyond the reach of formal EWS. Documented in §4.2.1.');

-- ----------------------------
-- Risk model
-- The hybrid model is the key recommendation of the study (§4.7, §5.1.4, §5.3 Rec. 1).
-- It integrates scientific indicators (rainfall_pct_normal, vci_3month, drought phase)
-- with indigenous indicator signals and requires joint elder-government validation (§4.7.2).
-- ----------------------------
INSERT INTO risk_models (name, version, description) VALUES
('STS Hybrid EWS Risk Model', '1.0',
 'Integrates KMD scientific indicators (rainfall % of normal, VCI, water pan levels) with '
 'NDMA drought phase assessments and community-validated indigenous knowledge signals '
 '(animal behaviour, vegetation, weather patterns, celestial indicators). '
 'Risk score is computed by RiskEngine.php and is intended to be reviewed and confirmed '
 'by a joint local committee of elders, chiefs, and government representatives before '
 'dissemination (§4.7.2). Primary dissemination channels: FM radio in Samburu/Kiswahili '
 'and chief/baraza meetings (§4.7.3).');

-- ----------------------------
-- Stakeholders
-- Organisations and community structures identified in §4.6.1 and §5.3.
-- location_id 1 = Samburu County; sub-county references use IDs 2–5.
-- stakeholder_type_id: 1=government, 2=ngo, 3=radio, 4=pastoralist, 5=intermediary
-- ----------------------------
INSERT INTO stakeholders (stakeholder_type_id, name, location_id) VALUES
(1, 'NDMA – Samburu County Office',          1),
(1, 'Kenya Meteorological Department (KMD)', 1),
(1, 'Samburu County Government',             1),
(2, 'Kenya Red Cross – Samburu County',      1),
(5, 'Samburu Council of Elders',             1),
(5, 'Samburu County Chiefs and Sub-Chiefs',  1);

-- ----------------------------
-- Official summaries
-- Grounded in research findings from §4.1.2 and §5.1.1.
-- KMD: below-average rainfall is the documented outlook context for Samburu,
--   particularly Samburu North which receives less rainfall than the west (§4.1.2).
-- NDMA: ALERT phase reflects the situation described by research participants,
--   with livestock body condition declining and preparedness actions needed (§4.3.2, §5.1.1).
-- Admin should update summary_text and valid_period via admin.php after reading each bulletin.
-- ----------------------------
INSERT INTO official_summaries (source_org, outlook_category, drought_phase, summary_text, valid_period, updated_at) VALUES
('KMD',
 'below_average',
 NULL,
 'KMD seasonal forecast indicates below-average rainfall for Samburu County. Samburu North sub-county is expected to receive significantly less rainfall than the county average, while Samburu West may approach near-average levels. Pastoralists in northern and eastern areas should prepare for early pasture failure and reduced water pan levels. Update this text via admin.php after reading the current KMD Monthly/Seasonal Forecast bulletin.',
 'Pending – update via admin.php',
 NOW()),

('NDMA',
 NULL,
 'ALERT',
 'NDMA assessment places Samburu County at ALERT phase. Pasture and water availability are below normal seasonal levels, livestock body condition is declining, and preparedness actions are required. Communities in remote grazing areas – particularly those beyond radio and network coverage – may be receiving this information late. Warnings should be relayed immediately through chiefs, elders, and community meetings. Update this text via admin.php after reading the current NDMA National Drought Situation Bulletin.',
 'Pending – update via admin.php',
 NOW());

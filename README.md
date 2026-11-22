# Hospital Management System

This repository contains the original PHP/MySQL appointment-management coursework project and a deployable Streamlit portfolio demo.

## Live demo

https://hospital-management-system-hude4n5sgn5ezqkdwrwfw6.streamlit.app/

[![Deploy on Streamlit](https://static.streamlit.io/badges/streamlit_badge_black_white.svg)](https://share.streamlit.io/)

**Deployment settings:** Select this repository, branch `main`, and main file path `app.py`. Streamlit will issue the public `*.streamlit.app` address after deployment. Add the generated address here once Streamlit has created it; no unverified demo URL is listed.

## Streamlit demo features

- Dashboard overview for doctors, patients and appointments.
- Doctor directory with specialty filtering and a session-only add form.
- Synthetic patient directory with search and session-only registration.
- Appointment register, filters, status updates and CSV export.
- Appointment booking with checks for past dates, unavailable doctors and duplicate doctor/time slots.
- All demo data is fictional and resets when the Streamlit session resets.

### Run locally

```powershell
py -m venv .venv
.venv\Scripts\Activate.ps1
pip install -r requirements.txt
streamlit run app.py
```

## Original PHP/MySQL application

The original coursework source remains in [`DBMS_18CSC303J_Hospital-management-system-main/`](DBMS_18CSC303J_Hospital-management-system-main/). It expects PHP, Apache and MySQL (for example XAMPP). Follow the SQL import instructions in that folder's README after configuring the database.

### PHP configuration

Set these environment variables for the PHP app:

- `DB_HOST` — MySQL host (defaults to `localhost`)
- `DB_PORT` — MySQL port (defaults to `3306`)
- `DB_NAME` — database name (defaults to `edoc`)
- `DB_USER` — database user (defaults to `root`)
- `DB_PASSWORD` — database password (defaults to empty for local XAMPP only)

The PHP login flow accepts legacy plaintext demo passwords once and upgrades a successfully verified password to PHP's password hash format. New patient and doctor passwords are stored with `password_hash`. Replace default test credentials before any non-local use.

## Important safety notice

The Streamlit app is a fictional workflow demonstration, not a live hospital information system. Do not enter actual patient records, medical histories or contact details. It does not provide diagnosis, prescriptions, clinical decision support, or production-grade authentication/audit storage. Do not deploy the legacy PHP/MySQL source with its default credentials as a production healthcare service.

## Tests

```powershell
pip install -r requirements-dev.txt
pytest -q
```

GitHub Actions runs the Streamlit helper tests, Python syntax check and PHP syntax lint checks.

## License

The original PHP code retains its upstream notices in its subfolder. The Streamlit demo and supporting files are released under the MIT License in [LICENSE](LICENSE).

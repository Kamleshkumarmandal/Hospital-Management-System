# Hospital Management System

This repository contains the original PHP/MySQL appointment-management coursework project and a deployable Streamlit portfolio demo.

## Live demo

**Streamlit deployment target:** select this repository, branch `main`, and main file path `app.py` at [Streamlit Community Cloud](https://share.streamlit.io/). After deployment, copy the generated `*.streamlit.app` URL into this section. No public demo URL is claimed until Streamlit has actually created one.

## Streamlit demo features

- Dashboard overview for doctors, patients and appointments.
- Doctor directory with specialty filter and session-only add form.
- Synthetic patient directory with search and session-only registration.
- Appointment register, filters, status updates and CSV export.
- Appointment booking with validation against past dates, unavailable doctors and duplicate doctor time slots.
- All demo data is fictional and resets when the Streamlit session is reset.

### Run the demo locally

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

The PHP login flow accepts legacy plaintext demo passwords once and upgrades a successfully verified password to PHP's password hash format. Replace default test credentials before any non-local use.

## Important safety notice

The Streamlit app is a fictional workflow demonstration, not a live hospital information system. Do not enter actual patient records, medical histories or contact details. It does not provide diagnosis, prescribing, clinical decision support, or production-grade authentication/audit storage. Do not deploy the legacy PHP/MySQL source with its default credentials as a production healthcare service.

## Tests

```powershell
pip install -r requirements-dev.txt
pytest -q
```

The CI workflow runs the Streamlit helper tests and PHP syntax lint checks.

## License

The original PHP code retains its upstream notices in its subfolder. The Streamlit demo and supporting files are released under the MIT License in [LICENSE](LICENSE).

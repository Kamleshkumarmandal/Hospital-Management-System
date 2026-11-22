from datetime import date, timedelta

import pytest

from hospital_demo import (
    add_appointment,
    dashboard_metrics,
    make_demo_data,
    update_appointment_status,
)


def test_demo_data_is_synthetic_and_has_expected_tables():
    doctors,patients,appointments=make_demo_data(today=date(2026,11,22))
    assert len(doctors)>=5
    assert len(patients)>=10
    assert len(appointments)>=10
    assert "diagnosis" not in patients.columns


def test_add_appointment_creates_scheduled_booking():
    doctors,patients,appointments=make_demo_data(today=date(2026,11,22))
    updated=add_appointment(
        appointments,doctors,patients,
        patient_id=patients.iloc[0]["patient_id"],
        doctor_id=doctors.iloc[0]["doctor_id"],
        appointment_date=date(2026,12,20),
        appointment_time="17:30",
        reason="Routine check-up",
        today=date(2026,11,22),
    )
    assert len(updated)==len(appointments)+1
    assert updated.iloc[-1]["status"]=="Scheduled"


def test_double_booking_same_doctor_slot_is_rejected():
    doctors,patients,appointments=make_demo_data(today=date(2026,11,22))
    doctor=doctors.iloc[0]["doctor_id"]
    patient=patients.iloc[0]["patient_id"]
    appt_date=date(2026,12,20)
    first=add_appointment(appointments,doctors,patients,patient,doctor,appt_date,"17:30","Check-up",today=date(2026,11,22))
    with pytest.raises(ValueError,match="already has an appointment"):
        add_appointment(first,doctors,patients,patients.iloc[1]["patient_id"],doctor,appt_date,"17:30","Follow-up",today=date(2026,11,22))


def test_cannot_book_past_date_or_unavailable_doctor():
    doctors,patients,appointments=make_demo_data(today=date(2026,11,22))
    with pytest.raises(ValueError,match="past"):
        add_appointment(appointments,doctors,patients,patients.iloc[0]["patient_id"],doctors.iloc[0]["doctor_id"],date(2026,11,21),"10:00","Visit",today=date(2026,11,22))
    away=doctors[doctors["availability"]!="Available"].iloc[0]["doctor_id"]
    with pytest.raises(ValueError,match="not currently available"):
        add_appointment(appointments,doctors,patients,patients.iloc[0]["patient_id"],away,date(2026,12,20),"10:00","Visit",today=date(2026,11,22))


def test_appointment_status_update_and_dashboard_metrics():
    doctors,patients,appointments=make_demo_data(today=date(2026,11,22))
    appointment_id=appointments.iloc[0]["appointment_id"]
    updated=update_appointment_status(appointments,appointment_id,"Completed")
    assert updated.loc[updated["appointment_id"]==appointment_id,"status"].iloc[0]=="Completed"
    metrics=dashboard_metrics(doctors,patients,updated)
    assert metrics["doctors"]==len(doctors)
    assert metrics["patients"]==len(patients)
    assert metrics["appointments"]==len(appointments)

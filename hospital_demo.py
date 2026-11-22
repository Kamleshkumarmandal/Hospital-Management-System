"""Session-based synthetic hospital demo data and appointment helpers."""
from __future__ import annotations

from datetime import date, time
import pandas as pd


def make_demo_data(today: date | None = None):
    today = today or date.today()
    doctors = pd.DataFrame([
        {"doctor_id":"D-101","doctor_name":"Dr. Aanya Sharma","specialty":"Cardiology","email":"aanya@example.test","phone":"9000000101","availability":"Available"},
        {"doctor_id":"D-102","doctor_name":"Dr. Rohan Mehta","specialty":"Dermatology","email":"rohan@example.test","phone":"9000000102","availability":"Available"},
        {"doctor_id":"D-103","doctor_name":"Dr. Priya Nair","specialty":"Paediatrics","email":"priya@example.test","phone":"9000000103","availability":"On leave"},
        {"doctor_id":"D-104","doctor_name":"Dr. Arjun Iyer","specialty":"Orthopaedics","email":"arjun@example.test","phone":"9000000104","availability":"Available"},
        {"doctor_id":"D-105","doctor_name":"Dr. Sara Khan","specialty":"Neurology","email":"sara@example.test","phone":"9000000105","availability":"Available"},
        {"doctor_id":"D-106","doctor_name":"Dr. Dev Patel","specialty":"General Medicine","email":"dev@example.test","phone":"9000000106","availability":"Available"},
    ])
    patients = pd.DataFrame([
        {"patient_id":f"P-{2001+i}","patient_name":name,"age":age,"gender":gender,"phone":f"9000000{200+i:04d}","city":city}
        for i,(name,age,gender,city) in enumerate([
            ("Aarav Rao",34,"Male","Chennai"),("Diya Kapoor",27,"Female","Bengaluru"),
            ("Ishaan Das",51,"Male","Hyderabad"),("Mira Sen",42,"Female","Pune"),
            ("Kabir Shah",19,"Male","Mumbai"),("Anaya Bose",63,"Female","Kolkata"),
            ("Reyansh Iyer",8,"Male","Chennai"),("Tara Nair",37,"Female","Kochi"),
            ("Vivaan Patel",46,"Male","Ahmedabad"),("Myra Reddy",29,"Female","Coimbatore"),
            ("Advik Khan",56,"Male","Delhi"),("Saanvi Paul",31,"Female","Jaipur"),
        ])
    ])
    statuses = ["Scheduled","Completed","Scheduled","Cancelled","Scheduled","Completed","Scheduled","Scheduled","Completed","Scheduled","Cancelled","Scheduled"]
    appointment_rows=[]
    for i in range(18):
        patient=patients.iloc[i % len(patients)]
        doctor=doctors.iloc[(i*3) % len(doctors)]
        appt_day=today + pd.Timedelta(days=(i % 12)-3)
        appointment_rows.append({
            "appointment_id":f"A-{3001+i}",
            "patient_id":patient["patient_id"],
            "patient_name":patient["patient_name"],
            "doctor_id":doctor["doctor_id"],
            "doctor_name":doctor["doctor_name"],
            "specialty":doctor["specialty"],
            "appointment_date":pd.Timestamp(appt_day).date(),
            "appointment_time":f"{9+(i%8):02d}:{'00' if i%2==0 else '30'}",
            "reason":["General consultation","Follow-up visit","Annual check-up","Specialist consultation"][i%4],
            "status":statuses[i % len(statuses)],
        })
    appointments=pd.DataFrame(appointment_rows)
    return doctors, patients, appointments


def add_appointment(
    appointments: pd.DataFrame,
    doctors: pd.DataFrame,
    patients: pd.DataFrame,
    patient_id: str,
    doctor_id: str,
    appointment_date: date,
    appointment_time: str,
    reason: str,
    today: date | None = None,
) -> pd.DataFrame:
    """Validate and append one appointment to the demo session's DataFrame."""
    today=today or date.today()
    if patient_id not in set(patients["patient_id"]):
        raise ValueError("Select a valid patient.")
    if doctor_id not in set(doctors["doctor_id"]):
        raise ValueError("Select a valid doctor.")
    doctor=doctors.loc[doctors["doctor_id"]==doctor_id].iloc[0]
    if doctor["availability"] != "Available":
        raise ValueError("This doctor is not currently available.")
    if appointment_date < today:
        raise ValueError("Appointment date cannot be in the past.")
    if not reason.strip():
        raise ValueError("Enter a short appointment reason.")

    occupied=appointments[
        (appointments["doctor_id"]==doctor_id)
        & (appointments["appointment_date"]==appointment_date)
        & (appointments["appointment_time"]==appointment_time)
        & (appointments["status"]!="Cancelled")
    ]
    if not occupied.empty:
        raise ValueError("This doctor already has an appointment in that time slot.")

    patient=patients.loc[patients["patient_id"]==patient_id].iloc[0]
    new_row={
        "appointment_id":f"A-{3001 + len(appointments)}",
        "patient_id":patient_id,
        "patient_name":patient["patient_name"],
        "doctor_id":doctor_id,
        "doctor_name":doctor["doctor_name"],
        "specialty":doctor["specialty"],
        "appointment_date":appointment_date,
        "appointment_time":appointment_time,
        "reason":reason.strip(),
        "status":"Scheduled",
    }
    return pd.concat([appointments,pd.DataFrame([new_row])],ignore_index=True)


def update_appointment_status(appointments: pd.DataFrame, appointment_id: str, new_status: str) -> pd.DataFrame:
    allowed={"Scheduled","Completed","Cancelled","No-show"}
    if new_status not in allowed:
        raise ValueError("Invalid appointment status.")
    if appointment_id not in set(appointments["appointment_id"]):
        raise ValueError("Appointment not found.")
    result=appointments.copy()
    result.loc[result["appointment_id"]==appointment_id,"status"]=new_status
    return result


def dashboard_metrics(doctors: pd.DataFrame, patients: pd.DataFrame, appointments: pd.DataFrame) -> dict[str,int]:
    active=appointments[appointments["status"]=="Scheduled"]
    today=date.today()
    return {
        "doctors":int(len(doctors)),
        "patients":int(len(patients)),
        "appointments":int(len(appointments)),
        "upcoming":int((active["appointment_date"]>=today).sum()),
    }

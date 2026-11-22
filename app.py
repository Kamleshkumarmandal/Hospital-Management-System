"""Hospital Management System — synthetic interactive portfolio demo."""
from __future__ import annotations

from datetime import date, timedelta

import pandas as pd
import plotly.express as px
import streamlit as st

from hospital_demo import (
    add_appointment,
    dashboard_metrics,
    make_demo_data,
    update_appointment_status,
)

st.set_page_config(page_title="Hospital Management System", page_icon="🏥", layout="wide")

st.markdown("""
<style>
.block-container {padding-top: 1.7rem; padding-bottom: 2.5rem;}
[data-testid="stMetric"] {background:#f4f8fc;border:1px solid #e3edf6;padding:14px 16px;border-radius:14px;}
[data-testid="stSidebar"] {background:#f7f9fc;}
</style>
""", unsafe_allow_html=True)

if "hospital_data_initialized" not in st.session_state:
    doctors, patients, appointments = make_demo_data()
    st.session_state.doctors=doctors
    st.session_state.patients=patients
    st.session_state.appointments=appointments
    st.session_state.hospital_data_initialized=True

doctors=st.session_state.doctors
patients=st.session_state.patients
appointments=st.session_state.appointments

with st.sidebar:
    st.title("🏥 CarePoint")
    st.caption("Hospital Operations Demo")
    page=st.radio("Navigation",["Dashboard","Doctors","Patients","Appointments","Book Appointment"])
    st.divider()
    st.info("Demo workspace uses fictional records. Changes last for this browser session only.")
    if st.button("Reset demo data",use_container_width=True):
        doctors,patients,appointments=make_demo_data()
        st.session_state.doctors=doctors
        st.session_state.patients=patients
        st.session_state.appointments=appointments
        st.rerun()

st.title("Hospital Management System")
st.caption("A simple workspace for managing doctors, patients and appointments.")
st.warning("Portfolio demo only — all names and records are synthetic. Do not enter real patient or medical information.",icon="⚠️")

if page=="Dashboard":
    metrics=dashboard_metrics(doctors,patients,appointments)
    a,b,c,d=st.columns(4)
    a.metric("Doctors",metrics["doctors"])
    b.metric("Patients",metrics["patients"])
    c.metric("Appointments",metrics["appointments"])
    d.metric("Upcoming",metrics["upcoming"])

    st.subheader("Appointment overview")
    left,right=st.columns(2)
    with left:
        status_counts=appointments["status"].value_counts().rename_axis("Status").reset_index(name="Appointments")
        fig=px.pie(status_counts,names="Status",values="Appointments",hole=.55)
        st.plotly_chart(fig,use_container_width=True)
    with right:
        specialty_counts=doctors["specialty"].value_counts().rename_axis("Specialty").reset_index(name="Doctors")
        fig=px.bar(specialty_counts,x="Specialty",y="Doctors",color="Specialty")
        fig.update_layout(showlegend=False,xaxis_title="")
        st.plotly_chart(fig,use_container_width=True)

    st.subheader("Next appointments")
    upcoming=appointments[
        (appointments["status"]=="Scheduled")
        & (appointments["appointment_date"]>=date.today())
    ].sort_values(["appointment_date","appointment_time"]).head(8)
    st.dataframe(upcoming[["appointment_id","appointment_date","appointment_time","patient_name","doctor_name","specialty","status"]],use_container_width=True,hide_index=True)

elif page=="Doctors":
    st.subheader("Doctor directory")
    specialties=["All"]+sorted(doctors["specialty"].unique().tolist())
    selected=st.selectbox("Filter by specialty",specialties)
    view=doctors if selected=="All" else doctors[doctors["specialty"]==selected]
    st.dataframe(view,use_container_width=True,hide_index=True)
    st.download_button("Download doctor list",doctors.to_csv(index=False).encode("utf-8"),"doctors_demo.csv","text/csv")
    with st.expander("Add a doctor"):
        with st.form("add_doctor_form",clear_on_submit=True):
            name=st.text_input("Doctor name")
            specialty=st.text_input("Specialty")
            email=st.text_input("Demo email")
            phone=st.text_input("Demo phone")
            submitted=st.form_submit_button("Add doctor")
        if submitted:
            if not name.strip() or not specialty.strip():
                st.error("Enter the doctor's name and specialty.")
            else:
                new_id=f"D-{101+len(doctors)}"
                new=pd.DataFrame([{"doctor_id":new_id,"doctor_name":name.strip(),"specialty":specialty.strip(),"email":email.strip(),"phone":phone.strip(),"availability":"Available"}])
                st.session_state.doctors=pd.concat([doctors,new],ignore_index=True)
                st.success(f"Added {name.strip()}.")
                st.rerun()

elif page=="Patients":
    st.subheader("Patient directory")
    query=st.text_input("Search by patient name, ID or city")
    view=patients.copy()
    if query.strip():
        mask=(
            view["patient_name"].str.contains(query,case=False,na=False)
            | view["patient_id"].str.contains(query,case=False,na=False)
            | view["city"].str.contains(query,case=False,na=False)
        )
        view=view[mask]
    st.dataframe(view,use_container_width=True,hide_index=True)
    st.download_button("Download patient list",patients.to_csv(index=False).encode("utf-8"),"patients_demo.csv","text/csv")
    with st.expander("Register a demo patient"):
        with st.form("add_patient_form",clear_on_submit=True):
            name=st.text_input("Patient name")
            age=st.number_input("Age",min_value=0,max_value=120,value=25)
            gender=st.selectbox("Gender",["Female","Male","Other","Prefer not to say"])
            phone=st.text_input("Demo contact number")
            city=st.text_input("City")
            submitted=st.form_submit_button("Register patient")
        if submitted:
            if not name.strip():
                st.error("Enter a patient name.")
            else:
                new_id=f"P-{2001+len(patients)}"
                new=pd.DataFrame([{"patient_id":new_id,"patient_name":name.strip(),"age":int(age),"gender":gender,"phone":phone.strip(),"city":city.strip()}])
                st.session_state.patients=pd.concat([patients,new],ignore_index=True)
                st.success(f"Registered {name.strip()}.")
                st.rerun()

elif page=="Appointments":
    st.subheader("Appointment register")
    f1,f2=st.columns(2)
    status_filter=f1.multiselect("Status",["Scheduled","Completed","Cancelled","No-show"],default=["Scheduled","Completed","Cancelled","No-show"])
    specialty_filter=f2.multiselect("Specialty",sorted(appointments["specialty"].unique()),default=sorted(appointments["specialty"].unique()))
    view=appointments[
        appointments["status"].isin(status_filter)
        & appointments["specialty"].isin(specialty_filter)
    ].sort_values(["appointment_date","appointment_time"],ascending=[True,True])
    st.dataframe(view,use_container_width=True,hide_index=True)
    st.download_button("Download appointment register",view.to_csv(index=False).encode("utf-8"),"appointments_demo.csv","text/csv")
    st.divider()
    st.markdown("**Update appointment status**")
    if not appointments.empty:
        selected_id=st.selectbox("Appointment ID",appointments["appointment_id"].tolist())
        new_status=st.selectbox("New status",["Scheduled","Completed","Cancelled","No-show"])
        if st.button("Update status"):
            st.session_state.appointments=update_appointment_status(appointments,selected_id,new_status)
            st.success("Appointment status updated.")
            st.rerun()

elif page=="Book Appointment":
    st.subheader("Book a new appointment")
    st.caption("Select a demo patient, an available doctor and an open time slot.")
    available=doctors[doctors["availability"]=="Available"]
    with st.form("book_appointment_form",clear_on_submit=True):
        patient_label=st.selectbox("Patient",patients["patient_id"]+" — "+patients["patient_name"])
        doctor_label=st.selectbox("Doctor",available["doctor_id"]+" — "+available["doctor_name"]+" ("+available["specialty"]+")")
        appt_date=st.date_input("Appointment date",value=date.today()+timedelta(days=1),min_value=date.today())
        appt_time=st.selectbox("Appointment time",["09:00","09:30","10:00","10:30","11:00","11:30","12:00","14:00","14:30","15:00","15:30","16:00"])
        reason=st.text_area("Reason for visit",placeholder="Example: Routine follow-up")
        submitted=st.form_submit_button("Confirm demo appointment",type="primary")
    if submitted:
        patient_id=patient_label.split(" — ",1)[0]
        doctor_id=doctor_label.split(" — ",1)[0]
        try:
            st.session_state.appointments=add_appointment(
                appointments,doctors,patients,patient_id,doctor_id,appt_date,appt_time,reason
            )
            st.success("Appointment booked in this demo session.")
            st.rerun()
        except ValueError as exc:
            st.error(str(exc))

st.divider()
st.caption("CarePoint is a non-clinical portfolio demo. It does not provide diagnosis, prescriptions, medical advice or secure medical-record storage.")

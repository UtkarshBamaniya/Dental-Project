const toDateObject = (value) => {
    if (!value) {
        return null;
    }

    return value instanceof Date ? value : new Date(value);
};

const toTimeObject = (value) => {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return value;
    }

    const [hours = '0', minutes = '0', seconds = '0'] = String(value).split(':');
    const time = new Date();
    time.setHours(Number(hours), Number(minutes), Number(seconds), 0);

    return time;
};

export const genderOptions = ['Male', 'Female', 'Other'];
export const statusOptions = ['Active', 'Inactive'];
export const bloodGroupOptions = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
export const problemAreaOptions = ['Upper Left', 'Upper Right', 'Lower Left', 'Lower Right', 'Full Mouth'];
export const priorityOptions = ['Normal', 'Urgent', 'Emergency'];
export const appointmentStatusOptions = ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled', 'No Show'];
export const paymentModeOptions = ['Cash', 'UPI', 'Card', 'Bank Transfer'];
export const paymentStatusOptions = ['Pending', 'Partial', 'Paid'];
export const visitTypeOptions = ['First Visit', 'Follow-up'];

export const createPatientForm = (patient = null) => ({
    id: patient?.id ?? null,
    patient_code: patient?.patient_code ?? '',
    first_name: patient?.first_name ?? '',
    middle_name: patient?.middle_name ?? '',
    last_name: patient?.last_name ?? '',
    gender: patient?.gender ?? null,
    date_of_birth: toDateObject(patient?.date_of_birth),
    age: patient?.age ?? null,
    mobile_no: patient?.mobile_no ?? '',
    alternate_mobile_no: patient?.alternate_mobile_no ?? '',
    email: patient?.email ?? '',
    address: patient?.address ?? '',
    city: patient?.city ?? '',
    state: patient?.state ?? '',
    pincode: patient?.pincode ?? '',
    occupation: patient?.occupation ?? '',
    referred_by: patient?.referred_by ?? '',
    status: patient?.status ?? 'Active',
});

export const createMedicalHistoryForm = (medicalHistory = null) => ({
    blood_group: medicalHistory?.blood_group ?? null,
    medical_id: medicalHistory?.medical_id ?? [],
    allergy_details: medicalHistory?.allergy_details ?? '',
    current_medicine: medicalHistory?.current_medicine ?? '',
    previous_dental_treatment: medicalHistory?.previous_dental_treatment ?? '',
    other_medical_notes: medicalHistory?.other_medical_notes ?? '',
});

export const createAppointmentForm = (appointment = null, defaults = {}) => ({
    id: appointment?.id ?? null,
    appointment_no: appointment?.appointment_no ?? null,
    appointment_date: toDateObject(appointment?.appointment_date ?? defaults.appointment_date ?? null),
    appointment_time: toTimeObject(appointment?.appointment_time ?? defaults.appointment_time ?? null),
    doctor_id: appointment?.doctor_id ?? defaults.doctor_id ?? null,
    visit_type: appointment?.visit_type ?? defaults.visit_type ?? 'First Visit',
    appointment_type_id: appointment?.appointment_type_id ?? defaults.appointment_type_id ?? null,
    chief_complaint: appointment?.chief_complaint ?? '',
    problem_area: appointment?.problem_area ?? defaults.problem_area ?? null,
    tooth_no: appointment?.tooth_no ?? '',
    priority: appointment?.priority ?? defaults.priority ?? 'Normal',
    status: appointment?.status ?? defaults.status ?? 'Scheduled',
    notes: appointment?.notes ?? '',
});

export const createBillingForm = (billing = null) => ({
    consultation_fee: billing?.consultation_fee ?? 0,
    treatment_estimate: billing?.treatment_estimate ?? 0,
    discount: billing?.discount ?? 0,
    paid_amount: billing?.paid_amount ?? 0,
    payment_mode: billing?.payment_mode ?? null,
    payment_status: billing?.payment_status ?? 'Pending',
    remarks: billing?.remarks ?? '',
});

export const createPatientAppointmentForm = (payload = null) => ({
    ...createPatientForm(payload),
    medical_history: createMedicalHistoryForm(payload?.medical_history),
    appointment: createAppointmentForm(payload?.appointment),
    billing: createBillingForm(payload?.billing),
});

export const createFollowUpForm = (appointment = null) => ({
    appointment: createAppointmentForm(appointment, {
        visit_type: 'Follow-up',
        priority: 'Normal',
        status: 'Scheduled',
    }),
    billing: createBillingForm(appointment?.billing),
});

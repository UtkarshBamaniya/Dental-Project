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
export const appointmentTypeOptions = [
    'Consultation',
    'Cleaning',
    'Root Canal',
    'Extraction',
    'Filling',
    'Braces Consultation',
    'Crown / Bridge',
    'Implant Consultation',
    'Follow-up',
];
export const problemAreaOptions = ['Upper Left', 'Upper Right', 'Lower Left', 'Lower Right', 'Full Mouth'];
export const priorityOptions = ['Normal', 'Urgent', 'Emergency'];
export const appointmentStatusOptions = ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled', 'No Show'];
export const paymentModeOptions = ['Cash', 'UPI', 'Card', 'Bank Transfer'];
export const paymentStatusOptions = ['Pending', 'Partial', 'Paid'];
export const visitTypeOptions = ['First Visit', 'Follow-up'];

export const createPatientAppointmentForm = (patient = null) => ({
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

    medical_history: {
        blood_group: patient?.medical_history?.blood_group ?? null,
        diabetes: patient?.medical_history?.diabetes ?? false,
        blood_pressure: patient?.medical_history?.blood_pressure ?? false,
        heart_disease: patient?.medical_history?.heart_disease ?? false,
        allergy: patient?.medical_history?.allergy ?? false,
        allergy_details: patient?.medical_history?.allergy_details ?? '',
        current_medicine: patient?.medical_history?.current_medicine ?? '',
        previous_dental_treatment: patient?.medical_history?.previous_dental_treatment ?? '',
        pregnancy_status: patient?.medical_history?.pregnancy_status ?? false,
        other_medical_notes: patient?.medical_history?.other_medical_notes ?? '',
    },

    appointment: {
        id: patient?.appointment?.id ?? null,
        appointment_date: toDateObject(patient?.appointment?.appointment_date),
        appointment_time: toTimeObject(patient?.appointment?.appointment_time),
        doctor_id: patient?.appointment?.doctor_id ?? null,
        visit_type: patient?.appointment?.visit_type ?? 'First Visit',
        appointment_type: patient?.appointment?.appointment_type ?? null,
        chief_complaint: patient?.appointment?.chief_complaint ?? '',
        problem_area: patient?.appointment?.problem_area ?? null,
        tooth_no: patient?.appointment?.tooth_no ?? '',
        priority: patient?.appointment?.priority ?? 'Normal',
        status: patient?.appointment?.status ?? 'Scheduled',
        notes: patient?.appointment?.notes ?? '',
    },

    billing: {
        consultation_fee: patient?.billing?.consultation_fee ?? 0,
        treatment_estimate: patient?.billing?.treatment_estimate ?? 0,
        discount: patient?.billing?.discount ?? 0,
        paid_amount: patient?.billing?.paid_amount ?? 0,
        payment_mode: patient?.billing?.payment_mode ?? null,
        payment_status: patient?.billing?.payment_status ?? 'Pending',
        remarks: patient?.billing?.remarks ?? '',
    },
});

export const createFollowUpForm = () => ({
    appointment: {
        appointment_date: null,
        appointment_time: null,
        doctor_id: null,
        visit_type: 'Follow-up',
        appointment_type: 'Follow-up',
        chief_complaint: '',
        problem_area: null,
        tooth_no: '',
        priority: 'Normal',
        status: 'Scheduled',
        notes: '',
    },
    billing: {
        consultation_fee: 0,
        treatment_estimate: 0,
        discount: 0,
        paid_amount: 0,
        payment_mode: null,
        payment_status: 'Pending',
        remarks: '',
    },
});

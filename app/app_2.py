import streamlit as st
import pandas as pd
import numpy as np
import scipy.signal as signal
import joblib
from tensorflow.keras.models import load_model
import json

# Paths to artifacts
MODEL_PATH = 'models/cnn_model.h5'
SCALER_PATH = 'models/scaler.pkl'
ENCODER_PATH = 'models/label_encoder.pkl'
METRICS_PATH = 'models/metrics.json'
SAMPLE_FALL = 'samples/window_002_FALL.csv'

# Dtype and sensor columns
dtype_map = {
    "subject_id": "int16", "trial": "int16",
    "acc_x": "float32","acc_y": "float32","acc_z": "float32",
    "gyro_x": "float32","gyro_y": "float32","gyro_z": "float32",
    "azimuth": "float32","pitch": "float32","roll": "float32"
}
sensor_cols = [
    'acc_x','acc_y','acc_z',
    'gyro_x','gyro_y','gyro_z',
    'azimuth','pitch','roll'
]

# Caching resource-loading functions
@st.cache_resource
def load_cnn():
    return load_model(MODEL_PATH)

@st.cache_resource
def load_scaler():
    return joblib.load(SCALER_PATH)

@st.cache_resource
def load_encoder():
    return joblib.load(ENCODER_PATH)

# Caching small data loads
@st.cache_data
def load_metrics():
    with open(METRICS_PATH, 'r') as f:
        return json.load(f)

# Caching data transformation
@st.cache_data
def apply_low_pass_filter(data, cutoff=3, fs=10, order=4):
    nyq = 0.5 * fs
    norm_cut = cutoff / nyq
    b, a = signal.butter(order, norm_cut, btype='low')
    df = data.copy()
    for c in sensor_cols[:6]:
        df[c] = signal.filtfilt(b, a, df[c].values)
    return df

# Preprocess single window
def preprocess_window(df):
    df = apply_low_pass_filter(df)
    scaler = load_scaler()
    df[sensor_cols] = scaler.transform(df[sensor_cols])
    return df[sensor_cols].values

# Predict label and probability
def predict_window(window_array):
    model = load_cnn()
    encoder = load_encoder()
    X = np.expand_dims(window_array, axis=0)
    y_prob = model.predict(X)
    idx = int(np.argmax(y_prob, axis=1)[0])
    label = encoder.inverse_transform([idx])[0]
    confidence = float(np.max(y_prob))
    return label, confidence

# Main app
st.set_page_config(page_title='Elderly Fall Detection', layout='wide')
st.title('Elderly Fall Detection Dashboard')

# KPI Panel
metrics = load_metrics()
col1, col2, col3, col4 = st.columns(4)
col1.metric('Accuracy',         f"{metrics['accuracy']*100:.1f}%")
col2.metric('Sensitivity',      f"{metrics['sensitivity']*100:.1f}%")
col3.metric('Specificity',      f"{metrics['specificity']*100:.1f}%")
col4.metric('False Alarm Rate', f"{metrics['false_alarm_rate']*100:.1f}%")

st.markdown('---')
# Emergency Alert Simulation
st.header('Emergency Alert Simulation')
if st.button('Simulate Fall'):
    try:
        df_sample = pd.read_csv(SAMPLE_FALL, dtype=dtype_map)
        vals = preprocess_window(df_sample)
        label, conf = predict_window(vals)
        if label == 'FALL':
            st.error('🚨 FALL DETECTED! 🚨')
            st.write('Notifying emergency contact: **John Doe** (+61 0234-567-890)')
        else:
            st.success('No fall detected in this window.')
        st.write(f'**Model Confidence:** {conf*100:.2f}%')
    except Exception as e:
        st.error(f'Error during simulation: {e}')

st.markdown('---')
# User Upload Section
st.header('Predict Activity from Uploaded Window')
uploaded = st.file_uploader('Upload CSV window', type='csv')

if uploaded:
    try:
        df_upload = pd.read_csv(uploaded, dtype=dtype_map)
        st.write(f'Uploaded window size: {df_upload.shape[0]} rows')
        vals_up = preprocess_window(df_upload)
        label_up, conf_up = predict_window(vals_up)
        st.subheader('Prediction Result')
        if label_up == 'FALL':
            st.error('❗ FALL detected! ❗')
        else:
            st.success('✅ No fall detected.')
        st.write(f'**Model Confidence:** {conf_up*100:.2f}%')
    except Exception as e:
        st.error(f'Failed to process uploaded file: {e}')

st.markdown('---')
st.write('Developed for stakeholder demo:')

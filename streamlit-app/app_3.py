import streamlit as st
import pandas as pd
import numpy as np
import scipy.signal as signal
import joblib
from tensorflow.keras.models import load_model
import json
import matplotlib.pyplot as plt

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
# Playback parameters
PLAY_WINDOW = 500
PLAY_STEP   = 50

# ─── Caching resource-loading functions ─────────────────────────────────────────
@st.cache_resource
def load_cnn():
    return load_model(MODEL_PATH)

@st.cache_resource
def load_scaler():
    return joblib.load(SCALER_PATH)

@st.cache_resource
def load_encoder():
    return joblib.load(ENCODER_PATH)

# ─── Caching data-loading functions ──────────────────────────────────────────────
@st.cache_data
def load_metrics():
    with open(METRICS_PATH, 'r') as f:
        return json.load(f)

@st.cache_data
def apply_low_pass_filter(df, cutoff=3, fs=10, order=4):
    nyq = 0.5 * fs
    norm = cutoff / nyq
    b, a = signal.butter(order, norm, btype='low')
    out = df.copy()
    for c in sensor_cols[:6]:
        out[c] = signal.filtfilt(b, a, df[c].values)
    return out

# ─── Preprocess and predict helper functions ────────────────────────────────────
def preprocess_window(df):
    df = apply_low_pass_filter(df)
    scaler = load_scaler()
    df[sensor_cols] = scaler.transform(df[sensor_cols])
    return df[sensor_cols].values

def predict_window(arr):
    model = load_cnn()
    encoder = load_encoder()
    X = np.expand_dims(arr, axis=0)
    y_prob = model.predict(X)
    idx = int(np.argmax(y_prob, axis=1)[0])
    label = encoder.inverse_transform([idx])[0]
    conf = float(np.max(y_prob))
    return label, conf

# ─── Streamlit App Layout ───────────────────────────────────────────────────────
st.set_page_config(page_title='Elderly Fall Detection', layout='wide')
st.title('Elderly Fall Detection Dashboard')

# KPI Panel
metrics = load_metrics()
cols = st.columns(4)
cols[0].metric('Accuracy',         f"{metrics['accuracy']*100:.1f}%")
cols[1].metric('Sensitivity',      f"{metrics['sensitivity']*100:.1f}%")
cols[2].metric('Specificity',      f"{metrics['specificity']*100:.1f}%")
cols[3].metric('False Alarm Rate', f"{metrics['false_alarm_rate']*100:.1f}%")
st.markdown('---')

# Emergency Alert Simulation
with st.expander('Emergency Alert Simulation'):
    if st.button('Simulate Fall'):
        try:
            df_s = pd.read_csv(SAMPLE_FALL, dtype=dtype_map)
            arr = preprocess_window(df_s)
            lbl, cf = predict_window(arr)
            if lbl == 'FALL':
                st.error('🚨 FALL DETECTED! 🚨')
                st.write('Notifying emergency contact: **John Doe** (+1 234-567-8901)')
            else:
                st.success('No fall detected in this window.')
            st.write(f'**Model Confidence:** {cf*100:.2f}%')
        except Exception as e:
            st.error(f'Error during simulation: {e}')

st.markdown('---')
# Single Window Upload
st.header('Predict from Single Window')
u = st.file_uploader('Upload CSV Window', type='csv')
if u:
    try:
        df_u = pd.read_csv(u, dtype=dtype_map)
        st.write(f'Window length: {df_u.shape[0]} rows')
        arr_u = preprocess_window(df_u)
        lbl_u, cf_u = predict_window(arr_u)
        st.subheader('Prediction Result')
        if lbl_u == 'FALL':
            st.error('❗ FALL detected! ❗')
        else:
            st.success('✅ No fall detected.')
        st.write(f'**Model Confidence:** {cf_u*100:.2f}%')
    except Exception as e:
        st.error(f'Failed to process uploaded window: {e}')

st.markdown('---')
# Real-Time Playback Mode
st.header('Real-Time Playback Mode')
f = st.file_uploader('Upload Full Sensor Trace (CSV)', type='csv', key='full')
if f:
    try:
        df_full = pd.read_csv(f, dtype=dtype_map)
        total = len(df_full)
        st.write(f'Total samples: {total}')
        # Filter and scale full trace
        df_filt = apply_low_pass_filter(df_full)
        df_filt[sensor_cols] = load_scaler().transform(df_filt[sensor_cols])
        # Compute slider range
        max_start = total - PLAY_WINDOW
        if max_start > 0:
            start = st.slider('Select Window Start', 0, max_start, 0, PLAY_STEP)
        elif max_start == 0:
            start = 0
            st.info(f'Trace exactly equals window size: start = 0')
        else:
            st.warning(f'Trace length ({total}) is shorter than window size ({PLAY_WINDOW}).')
            start = None
        # If a valid start is selected, proceed
        if start is not None:
            # Only select sensor columns for the window
            window_df = df_filt[sensor_cols].iloc[start : start + PLAY_WINDOW]
            # Prediction for this window
            lbl_p, cf_p = predict_window(window_df.values)
            # Plot accelerometer magnitude
            time_idx = np.arange(start, start + PLAY_WINDOW)
            mag = np.sqrt((window_df[['acc_x','acc_y','acc_z']]**2).sum(axis=1))
            fig, ax = plt.subplots()
            ax.plot(time_idx, mag)
            ax.set_title(f'Window {start}-{start+PLAY_WINDOW}: {lbl_p} ({cf_p*100:.1f}%)')
            ax.set_xlabel('Sample Index')
            ax.set_ylabel('Acceleration Magnitude')
            st.pyplot(fig)
    except Exception as e:
        st.error(f'Error in playback mode: {e}')
st.markdown('---')

st.write('Developed for stakeholder demo:')

import streamlit as st
import pandas as pd
import numpy as np
import scipy.signal as signal
import joblib
import json
from tensorflow.keras.models import load_model

# Paths to saved artifacts
MODEL_PATH = 'models/cnn_model.h5'
SCALER_PATH = 'models/scaler.pkl'
ENCODER_PATH = 'models/label_encoder.pkl'

with open("models/metrics.json") as f:
    m = json.load(f)

col1, col2, col3, col4 = st.columns(4)
col1.metric("Accuracy",           f"{m['accuracy']*100:.1f}%")
col2.metric("Sensitivity",        f"{m['sensitivity']*100:.1f}%")
col3.metric("Specificity",        f"{m['specificity']*100:.1f}%")
col4.metric("False-Alarm Rate",   f"{m['false_alarm_rate']*100:.1f}%")

# Data types and sensor columns
DTYPE_MAP = {
    "subject_id": "int16",
    "trial": "int16",
    "acc_x": "float32", "acc_y": "float32", "acc_z": "float32",
    "gyro_x": "float32","gyro_y": "float32","gyro_z": "float32",
    "azimuth": "float32", "pitch": "float32", "roll": "float32"
}
SENSOR_COLS = [
    'acc_x', 'acc_y', 'acc_z',
    'gyro_x', 'gyro_y', 'gyro_z',
    'azimuth', 'pitch', 'roll'
]

# Preprocessing: low-pass filter cached as data transformation
@st.cache_data
def apply_low_pass_filter(data, cutoff=3, fs=10, order=4):
    nyquist = 0.5 * fs
    normal_cutoff = cutoff / nyquist
    b, a = signal.butter(order, normal_cutoff, btype='low', analog=False)
    df_f = data.copy()
    for col in SENSOR_COLS[:6]:  # first six cols are acc and gyro
        df_f[col] = signal.filtfilt(b, a, data[col].values)
    return df_f

# Preprocess one window of data
def preprocess_window(df):
    df = apply_low_pass_filter(df)
    scaler = joblib.load(SCALER_PATH)
    df[SENSOR_COLS] = scaler.transform(df[SENSOR_COLS])
    return df[SENSOR_COLS].values

# Load model and encoder once, cached as resources
@st.cache_resource
def load_cnn():
    return load_model(MODEL_PATH)

@st.cache_resource
def load_encoder():
    return joblib.load(ENCODER_PATH)

# Main app

def main():
    st.title('Fall vs ADL Classification on a Sensor Window')
    st.write('Upload a single window of sensor data (CSV) and receive a prediction.')

    uploaded_file = st.file_uploader('Sensor window CSV', type='csv')
    if not uploaded_file:
        return

    df = pd.read_csv(uploaded_file, dtype=DTYPE_MAP)
    st.write(f'Uploaded data: {df.shape[0]} rows × {df.shape[1]} columns')

    try:
        data_array = preprocess_window(df)
    except Exception as e:
        st.error(f'Preprocessing failed: {e}')
        return

    X = np.expand_dims(data_array, axis=0)

    model = load_cnn()
    y_prob = model.predict(X)
    pred_idx = int(np.argmax(y_prob, axis=1)[0])
    encoder = load_encoder()
    pred_label = encoder.inverse_transform([pred_idx])[0]
    confidence = float(np.max(y_prob))

    st.subheader('Prediction')
    st.write(f'**Activity:** {pred_label}')
    st.write(f'**Confidence:** {confidence * 100:.2f}%')

if __name__ == '__main__':
    main()
#!/usr/bin/env python3

import pandas as pd
import numpy as np
import os

def analyze_pickle_file(file_path):
    """
    Analyze the contents of a pickle file containing a pandas DataFrame.
    """
    print(f"Analyzing pickle file: {file_path}")
    
    # Check if file exists
    if not os.path.exists(file_path):
        print(f"Error: File not found at {file_path}")
        return
    
    try:
        # Load the pickle file
        df = pd.read_pickle(file_path)
        
        # Basic DataFrame information
        print("\nDataFrame Info:")
        print("-" * 50)
        print(f"Shape: {df.shape}")
        print(f"Columns: {df.columns.tolist()}")
        print(f"Memory usage: {df.memory_usage(deep=True).sum() / 1024**2:.2f} MB")
        
        # Data types
        print("\nData Types:")
        print("-" * 50)
        print(df.dtypes)
        
        # Sample data
        print("\nFirst 5 rows:")
        print("-" * 50)
        print(df.head())
        
        # Basic statistics
        print("\nBasic Statistics:")
        print("-" * 50)
        print(df.describe())
        
        # Check for missing values
        print("\nMissing Values:")
        print("-" * 50)
        missing = df.isnull().sum()
        print(missing[missing > 0] if missing.any() else "No missing values found")
        
        # Value counts for categorical columns
        print("\nValue Counts for Categorical Columns:")
        print("-" * 50)
        for col in df.select_dtypes(include=['object']).columns:
            print(f"\n{col}:")
            print(df[col].value_counts())
        
        # Save sample to CSV for inspection
        sample_path = file_path.replace('.pkl', '_sample.csv')
        df.head(1000).to_csv(sample_path, index=False)
        print(f"\nSaved sample data to: {sample_path}")
        
    except Exception as e:
        print(f"Error analyzing pickle file: {str(e)}")

if __name__ == "__main__":
    # Path to the pickle file
    pickle_path = "data/df_filtered_binary.pkl"
    
    # Analyze the pickle file
    analyze_pickle_file(pickle_path)

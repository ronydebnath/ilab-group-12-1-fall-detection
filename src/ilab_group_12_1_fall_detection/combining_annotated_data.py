import os
import pandas as pd

# Define the base folder containing the data
folder_path = "../../data/raw/MobiAct_Dataset_v2.0/MobiAct_Dataset_v2.0/Annotated Data"

if not os.path.exists(folder_path):
    raise FileNotFoundError(f"Folder path does not exist: {folder_path}")

dfs = []
file_count = 0  # Track number of files processed

# Walk through all subdirectories and files
for root, _, files in os.walk(folder_path):
    for file in files:
        if file.endswith('.csv'):
            file_path = os.path.join(root, file)
            print(f"Processing file: {file_path}")  # Debugging output

            # Extract action, subject_id, and trial from filename
            parts = file.split('_')
            if len(parts) >= 4 and parts[-1].startswith("annotated.csv"):  
                subject_id = parts[1]  
                trial = parts[2]  
            else:
                print(f"Skipping file with unexpected format: {file}")
                continue  # Skip files that don't match expected format

            # Read CSV
            try:
                df = pd.read_csv(file_path)
                if df.empty:
                    print(f"Warning: Empty file skipped - {file}")
                    continue  # Skip empty files

                df['subject_id'] = subject_id
                df['trial'] = trial

                dfs.append(df)
                file_count += 1
            except Exception as e:
                print(f"Error reading file {file}: {e}")

# Check if we successfully loaded any files
if not dfs:
    raise ValueError("No valid CSV files were processed. Check file paths and formats.")

# Combine all DataFrames
combined_df = pd.concat(dfs, ignore_index=True)

# Save the combined dataset
output_file = "../../data/raw/MobiAct_combined.csv"
combined_df.to_csv(output_file, index=False)

print(f"Combined dataset saved as {output_file} with {file_count} files processed.")

// console.log("--- soundService.js MODULE EXECUTION STARTED ---");

// import { Audio } from 'expo-audio'; // This is the critical import
// console.log("--- soundService.js: Imported Audio object:", Audio); // <--- ADD THIS LOG

// import { Asset } from 'expo-asset';

// let soundObject = null;

// export const loadAlertSound = async () => {
//   console.log('SoundService: Attempting to load alert sound...');
//   if (soundObject !== null) {
//     console.log('SoundService: Sound already loaded.');
//     return;
//   }
//   try {
//     const asset = Asset.fromModule(require('../../assets/emergency_alert.mp3'));
//     if (!asset.downloaded) {
//       console.log('SoundService: Asset not downloaded, downloading now...');
//       await asset.downloadAsync();
//     }
//     console.log('SoundService: Asset URI:', asset.uri);
//     const { sound } = await Audio.Sound.createAsync(
//       asset,
//       {
//         // isLooping: true, // Uncomment if you want looping
//         volume: 1.0,
//       },
//       (status) => {
//         if (status.isLoaded && status.didJustFinish && !status.isLooping) {
//           console.log('SoundService: Sound finished playing.');
//         }
//         if (status.error) {
//           console.error(`SoundService: Playback Error: ${status.error}`);
//         }
//       }
//     );
//     soundObject = sound;
//     console.log('SoundService: Alert sound loaded successfully.');
//   } catch (error) {
//     console.error('SoundService: Failed to load alert sound', error);
//     soundObject = null;
//   }
// };

// export const playAlertSound = async () => {
//   console.log('SoundService: playAlertSound CALLED'); // For debugging
//   if (soundObject) {
//     try {
//       const status = await soundObject.getStatusAsync();
//       if (status.isLoaded && !status.isPlaying) {
//         console.log('SoundService: Playing sound...');
//         await soundObject.replayAsync();
//       } else if (status.isLoaded && status.isPlaying) {
//         console.log('SoundService: Sound already playing, restarting...');
//         await soundObject.replayAsync();
//       } else if (!status.isLoaded) {
//         console.warn('SoundService: playAlertSound called but sound not loaded. Attempting to load again.');
//         await loadAlertSound();
//         if (soundObject) {
//           console.log('SoundService: Playing sound after re-load...');
//           await soundObject.playAsync();
//         }
//       }
//     } catch (error) {
//       console.error('SoundService: Failed to play alert sound', error);
//     }
//   } else {
//     console.warn('SoundService: playAlertSound called but soundObject is null. Attempting to load and play.');
//     await loadAlertSound();
//     if (soundObject) {
//       await playAlertSound();
//     }
//   }
// };

// export const stopAlertSound = async () => {
//   console.log('SoundService: stopAlertSound CALLED'); // For debugging
//   if (soundObject) {
//     try {
//       const status = await soundObject.getStatusAsync();
//       if (status.isLoaded && status.isPlaying) {
//         console.log('SoundService: Stopping sound...');
//         await soundObject.stopAsync();
//         await soundObject.setPositionAsync(0);
//       }
//     } catch (error) {
//       console.error('SoundService: Failed to stop alert sound', error);
//     }
//   } else {
//     console.warn('SoundService: stopAlertSound called but soundObject is null.');
//   }
// };

// export const unloadAlertSound = async () => {
//   console.log('SoundService: unloadAlertSound CALLED'); // For debugging
//   if (soundObject) {
//     console.log('SoundService: Unloading alert sound...');
//     try {
//       await soundObject.unloadAsync();
//     } catch (error) {
//       console.error('SoundService: Failed to unload alert sound', error);
//     }
//     soundObject = null;
//   }
// };

// export const configureAudioMode = async () => { // Ensure 'export const'
//   console.log('SoundService: configureAudioMode CALLED'); // For debugging
//   try {
//     await Audio.setAudioModeAsync({
//       playsInSilentModeIOS: true,
//       allowsRecordingIOS: false,
//       shouldDuckAndroid: true,
//       staysActiveInBackground: false, // Set to true if you need background audio
//     });
//     console.log('SoundService: Audio mode configured successfully.');
//   } catch (error) {
//     console.error('SoundService: Failed to set audio mode', error);
//   }
// };

    // App.js
  // import React, { useEffect } from 'react';
  // import { View, Text, StyleSheet } from 'react-native';
  // import { Audio } from 'expo-audio'; // Attempt to import directly

  // export default function App() {
  //   console.log("!*!*!*!*! APP.JS IS RUNNING !*!*!*!*!");
  //   console.log("!*!*!*!*! App.js: Testing Audio import directly:", Audio); // Log it

  //   useEffect(() => {
  //     if (Audio && typeof Audio.setAudioModeAsync === 'function') {
  //       console.log("App.js: Audio object seems available and has setAudioModeAsync.");
  //       // You could try calling setAudioModeAsync here if the log above looks good
  //     } else {
  //       console.error("App.js: Audio object or setAudioModeAsync is UNDEFINED here!");
  //     }
  //   }, []);

  //   return (
  //     <View style={styles.container}>
  //       <Text style={styles.text}>Minimal Audio Import Test in App.js</Text>
  //       {Audio ? <Text>Audio object IS imported</Text> : <Text>Audio object IS UNDEFINED</Text>}
  //     </View>
  //   );
  // }

  // const styles = StyleSheet.create({
  //   container: {
  //     flex: 1,
  //     justifyContent: 'center',
  //     alignItems: 'center',
  //     backgroundColor: 'lightgoldenrodyellow',
  //   },
  //   text: {
  //     fontSize: 18,
  //     color: 'black',
  //     margin: 10,
  //   },
  // });
  

// src/services/soundService.js
// Using the older expo-av package
// console.log("--- soundService.js MODULE EXECUTION STARTED (using expo-av) ---");

// import { Audio } from 'expo-av';
// console.log("--- soundService.js: Imported Audio object from 'expo-av':", Audio);

// import { Asset } from 'expo-asset';

// let soundObject = null;

// // Define interruption mode constants based on expo-av (check documentation if these are incorrect for your version)
// // These might be different or might need Audio.InterruptionModeIOS etc. depending on the exact expo-av version.
// // It's often safer to rely on defaults if unsure.
// const INTERRUPTION_MODE_IOS_DO_NOT_MIX = 1; // Example value, check expo-av docs
// const INTERRUPTION_MODE_ANDROID_DO_NOT_MIX = 1; // Example value, check expo-av docs

// export const configureAudioMode = async () => {
//   console.log('SoundService: Configuring audio mode (expo-av)...');
//   try {
//     // Check if Audio and setAudioModeAsync exist
//     if (!Audio || typeof Audio.setAudioModeAsync !== 'function') {
//       console.error("SoundService: Audio object or setAudioModeAsync not available from expo-av.");
//       return;
//     }
//     await Audio.setAudioModeAsync({
//       allowsRecordingIOS: false,
//       // staysActiveInBackground: true, // Consider if needed, affects background audio policy
//       // Use the appropriate interruption mode constants for expo-av or omit if defaults are okay
//       // interruptionModeIOS: INTERRUPTION_MODE_IOS_DO_NOT_MIX, // Use the correct constant/enum for expo-av
//       playsInSilentModeIOS: true, // This is usually the most important one
//       shouldDuckAndroid: true,
//       // interruptionModeAndroid: INTERRUPTION_MODE_ANDROID_DO_NOT_MIX, // Use the correct constant/enum for expo-av
//       playThroughEarpieceAndroid: false
//     });
//     console.log('SoundService: Audio mode configured successfully (expo-av)');
//   } catch (error) {
//     console.error('SoundService: Failed to configure audio mode (expo-av)', error);
//   }
// };

// export const loadAlertSound = async () => {
//   console.log('SoundService: Attempting to load alert sound (expo-av)...');
//   if (soundObject !== null) {
//     console.log('SoundService: Sound already loaded.');
//     return;
//   }
//   try {
//     // Check if Audio and Audio.Sound exist
//     if (!Audio || !Audio.Sound) {
//       console.error("SoundService: Audio or Audio.Sound not available from expo-av.");
//       return;
//     }

//     // --- Debugging the asset require ---
//     const soundAssetPath = '../../assets/emergency_alert.mp3'; // Store path in variable
//     console.log(`SoundService: Attempting to require asset at path: ${soundAssetPath}`);
//     let requiredAsset;
//     try {
//         // Make sure the path is EXACTLY correct relative to this soundService.js file
//         // If soundService.js is in src/services/ and assets is at the root, ../../assets/... is correct.
//         requiredAsset = require(soundAssetPath);
//         console.log(`SoundService: Result of require('${soundAssetPath}'):`, requiredAsset); // Log the result
//     } catch (requireError) {
//         console.error(`SoundService: ERROR during require('${soundAssetPath}'):`, requireError);
//         console.error("SoundService: Please ensure the path is correct and the file exists at that location.");
//         return; // Stop if require itself throws an error
//     }

//     if (requiredAsset === undefined) {
//         console.error(`SoundService: ERROR - require('${soundAssetPath}') returned undefined. Check the file path and ensure the asset is included by the bundler.`);
//         return; // Stop execution if require failed silently
//     }
//     // --- End Debugging ---

//     // Ensure the path is correct relative to this file
//     const asset = Asset.fromModule(requiredAsset); // Pass the result of require to Asset.fromModule
//     if (!asset.downloaded) {
//       console.log('SoundService: Asset not downloaded, downloading now...');
//       await asset.downloadAsync();
//     }
//     console.log('SoundService: Asset URI:', asset.uri);
//     const { sound } = await Audio.Sound.createAsync(
//       asset, // Use the asset directly
//       {
//         // isLooping: true, // Uncomment if you want looping
//         volume: 1.0,
//       },
//       (status) => { // onPlaybackStatusUpdate callback
//         if (status.isLoaded && status.didJustFinish && !status.isLooping) {
//           console.log('SoundService: Sound finished playing.');
//         }
//         if (status.error) {
//           console.error(`SoundService: Playback Error: ${status.error}`);
//         }
//       }
//     );
//     soundObject = sound;
//     console.log('SoundService: Alert sound loaded successfully (expo-av).');
//   } catch (error) {
//     // Catch errors during Asset.fromModule or Audio.Sound.createAsync
//     console.error('SoundService: Failed to load alert sound (expo-av)', error);
//     soundObject = null;
//   }
// };

// export const playAlertSound = async () => {
//   console.log('SoundService: playAlertSound CALLED (expo-av)');
//   if (soundObject) {
//     try {
//       // Check if Audio object is available
//       if (!Audio) {
//         console.error("SoundService: Audio object not available from expo-av.");
//         return;
//       }
//       // Corrected typo: getStatusAsync
//       const status = await soundObject.getStatusAsync();
//       if (status.isLoaded && !status.isPlaying) {
//         console.log('SoundService: Playing sound...');
//         await soundObject.replayAsync();
//       } else if (status.isLoaded && status.isPlaying) {
//         console.log('SoundService: Sound already playing, restarting...');
//         await soundObject.replayAsync();
//       } else if (!status.isLoaded) {
//         console.warn('SoundService: playAlertSound called but sound not loaded. Attempting to load again.');
//         await loadAlertSound();
//         if (soundObject) {
//           console.log('SoundService: Playing sound after re-load...');
//           await soundObject.playAsync();
//         }
//       }
//     } catch (error) {
//       console.error('SoundService: Failed to play alert sound (expo-av)', error);
//     }
//   } else {
//     console.warn('SoundService: playAlertSound called but soundObject is null. Attempting to load and play.');
//     await loadAlertSound();
//     if (soundObject) {
//       await playAlertSound(); // Recursive call after loading
//     }
//   }
// };

// export const stopAlertSound = async () => {
//   console.log('SoundService: stopAlertSound CALLED (expo-av)');
//   if (soundObject) {
//     try {
//       // Check if Audio object is available
//       if (!Audio) {
//         console.error("SoundService: Audio object not available from expo-av.");
//         return;
//       }
//       const status = await soundObject.getStatusAsync();
//       if (status.isLoaded && status.isPlaying) {
//         console.log('SoundService: Stopping sound...');
//         await soundObject.stopAsync();
//         await soundObject.setPositionAsync(0);
//       }
//     } catch (error) {
//       console.error('SoundService: Failed to stop alert sound (expo-av)', error);
//     }
//   } else {
//     console.warn('SoundService: stopAlertSound called but soundObject is null.');
//   }
// };

// export const unloadAlertSound = async () => {
//   console.log('SoundService: unloadAlertSound CALLED (expo-av)');
//   if (soundObject) {
//     console.log('SoundService: Unloading alert sound...');
//     try {
//       // Check if Audio object is available
//       if (!Audio) {
//         console.error("SoundService: Audio object not available from expo-av.");
//         return;
//       }
//       await soundObject.unloadAsync();
//     } catch (error) {
//       console.error('SoundService: Failed to unload alert sound (expo-av)', error);
//     }
//     soundObject = null;
//   }
// };

console.log("--- soundService.js MODULE EXECUTION STARTED (using expo-av) ---");

import { Audio } from 'expo-av';
console.log("--- soundService.js: Imported Audio object from 'expo-av':", Audio);

import { Asset } from 'expo-asset';

let soundObject = null;

export const configureAudioMode = async () => {
  console.log('SoundService: Configuring audio mode (expo-av)...');
  try {
    // Ensure Audio object and its methods are available
    if (!Audio || typeof Audio.setAudioModeAsync !== 'function') {
      console.error("SoundService: Audio object or setAudioModeAsync not available from expo-av. Cannot configure audio mode.");
      return;
    }

    // Check if interruption mode constants are available before using them
    const interruptionModeIOS = Audio.INTERRUPTION_MODE_IOS_DO_NOT_MIX; // Standard expo-av enum
    const interruptionModeAndroid = Audio.INTERRUPTION_MODE_ANDROID_DO_NOT_MIX; // Standard expo-av enum

    if (interruptionModeIOS === undefined || interruptionModeAndroid === undefined) {
        console.warn("SoundService: Interruption mode constants are undefined. Proceeding without them.");
        await Audio.setAudioModeAsync({
            allowsRecordingIOS: false,
            playsInSilentModeIOS: true,
            shouldDuckAndroid: true,
            playThroughEarpieceAndroid: false,
            // staysActiveInBackground: true, // Consider if needed
        });
    } else {
        await Audio.setAudioModeAsync({
            allowsRecordingIOS: false,
            interruptionModeIOS: interruptionModeIOS,
            playsInSilentModeIOS: true,
            shouldDuckAndroid: true,
            interruptionModeAndroid: interruptionModeAndroid,
            playThroughEarpieceAndroid: false,
            // staysActiveInBackground: true, // Consider if needed
        });
    }
    console.log('SoundService: Audio mode configured successfully (expo-av)');
  } catch (error) {
    console.error('SoundService: Failed to configure audio mode (expo-av)', error);
  }
};

export const loadAlertSound = async () => {
  console.log('SoundService: Attempting to load alert sound (expo-av)...');
  if (soundObject !== null) {
    console.log('SoundService: Sound object already exists. Ensuring it is unloaded before reloading.');
    await unloadAlertSound(); // Ensure previous instance is cleared
  }

  try {
    if (!Audio || !Audio.Sound) {
      console.error("SoundService: Audio or Audio.Sound not available from expo-av. Cannot load sound.");
      return;
    }
    const soundAssetPath = '../../assets/sounds/emergency_alert.mp3'; // CRITICAL: VERIFY THIS PATH
    console.log(`SoundService: Attempting to require asset at path: "${soundAssetPath}"`);
    let requiredAssetModule;
    try {
      // This path MUST be correct relative to this soundService.js file.
      // Example: If soundService.js is in 'src/services/' and your assets are in 'assets/sounds/' at the project root,
      // then '../../assets/sounds/emergency_alert.mp3' would be correct.
      requiredAssetModule = require(soundAssetPath);
      console.log(`SoundService: Result of require('${soundAssetPath}'):`, requiredAssetModule);
    } catch (requireError) {
      console.error(`SoundService: CRITICAL ERROR during require('${soundAssetPath}'):`, requireError);
      console.error("SoundService: This means the file path is wrong, the file doesn't exist, or Metro bundler cannot find it. Please double-check the path and ensure the file is correctly placed in your project and included in assets by your bundler.");
      return; // Stop if require itself throws an error
    }

    if (requiredAssetModule === undefined) {
      console.error(`SoundService: CRITICAL ERROR - require('${soundAssetPath}') returned undefined. The asset was not found or not bundled. Check the file path and that the asset is correctly included in your project.`);
      return; // Stop execution if require failed silently
    }

    const asset = Asset.fromModule(requiredAssetModule);
    console.log('SoundService: Asset.fromModule created. Attempting to download asset...');
    if (!asset.downloaded) {
      console.log('SoundService: Asset not downloaded, downloading now...');
      await asset.downloadAsync();
    }
    console.log('SoundService: Asset URI:', asset.uri, 'Downloaded:', asset.downloaded);

    if (!asset.uri) {
        console.error('SoundService: Asset URI is null or undefined after download attempt. Cannot create sound.');
        return;
    }

    console.log('SoundService: Creating new sound instance...');
    const { sound } = await Audio.Sound.createAsync(
      asset, // Use the asset object directly
      {
        isLooping: true, // Set to true for continuous playback
        volume: 1.0,
      },
      (status) => { // onPlaybackStatusUpdate callback
        if (status.isLoaded && status.didJustFinish && !status.isLooping) {
          console.log('SoundService: Sound finished playing (unexpected for looping sound).');
        }
        if (status.error) {
          console.error(`SoundService: Playback Error: ${status.error}`);
        }
      }
    );
    soundObject = sound;
    console.log('SoundService: Alert sound loaded successfully and set to loop (expo-av).');
  } catch (error) {
    console.error('SoundService: Failed to load alert sound (expo-av). Error details:', error);
    soundObject = null;
  }
};

// playAlertSound, stopAlertSound, unloadAlertSound remain largely the same as your last version,
// but ensure they also check if 'Audio' is defined if they use it directly.
// For brevity, I'll assume they are similar to the previous correct versions,
// focusing on the load and configure functions that had errors.

export const playAlertSound = async () => {
  console.log('SoundService: playAlertSound CALLED (expo-av)');
  if (!soundObject) {
    console.warn('SoundService: soundObject is null in playAlertSound. Attempting to load first.');
    await loadAlertSound();
    if (!soundObject) {
      console.error('SoundService: Failed to load sound, cannot play.');
      return;
    }
  }
  try {
    const status = await soundObject.getStatusAsync();
    if (status.isLoaded && !status.isPlaying) {
      console.log('SoundService: Playing sound (looping)...');
      await soundObject.playAsync();
    } else if (status.isLoaded && status.isPlaying) {
      console.log('SoundService: Sound is already playing (looping). Restarting for good measure.');
      await soundObject.replayAsync(); // Or just let it continue
    } else if (!status.isLoaded) {
      console.warn('SoundService: playAlertSound called but sound not loaded. This should have been handled.');
    }
  } catch (error) {
    console.error('SoundService: Failed to play alert sound (expo-av)', error);
  }
};

export const stopAlertSound = async () => {
  console.log('SoundService: stopAlertSound CALLED (expo-av)');
  if (soundObject) {
    try {
      const status = await soundObject.getStatusAsync();
      if (status.isLoaded && status.isPlaying) {
        console.log('SoundService: Stopping sound...');
        await soundObject.stopAsync();
        await soundObject.setPositionAsync(0); // Reset position for next play
      } else {
        console.log('SoundService: stopAlertSound called, but sound was not playing or not loaded.');
      }
    } catch (error) {
      console.error('SoundService: Failed to stop alert sound (expo-av)', error);
    }
  } else {
    console.warn('SoundService: stopAlertSound called but soundObject is null.');
  }
};

export const unloadAlertSound = async () => {
  console.log('SoundService: unloadAlertSound CALLED (expo-av)');
  if (soundObject) {
    console.log('SoundService: Unloading alert sound...');
    try {
      await soundObject.unloadAsync();
    } catch (error) {
      console.error('SoundService: Failed to unload alert sound (expo-av)', error);
    }
    soundObject = null;
  }
};

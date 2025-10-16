type LegacyGetUserMedia = (
    constraints: MediaStreamConstraints,
    successCallback: (stream: MediaStream) => void,
    errorCallback: (error: unknown) => void,
) => void;

type LegacyNavigator = Navigator & {
    getUserMedia?: LegacyGetUserMedia;
    webkitGetUserMedia?: LegacyGetUserMedia;
    mozGetUserMedia?: LegacyGetUserMedia;
    msGetUserMedia?: LegacyGetUserMedia;
};

export const getMicrophoneStream = async (): Promise<MediaStream> => {
    const constraints: MediaStreamConstraints = {
        audio: {
            echoCancellation: true,
            noiseSuppression: true,
            channelCount: 1,
            sampleRate: 16000,
        },
    };

    if (navigator?.mediaDevices?.getUserMedia) {
        return navigator.mediaDevices.getUserMedia(constraints);
    }

    const legacyNavigator = navigator as LegacyNavigator;
    const legacyGetUserMedia =
        legacyNavigator.getUserMedia ??
        legacyNavigator.webkitGetUserMedia ??
        legacyNavigator.mozGetUserMedia ??
        legacyNavigator.msGetUserMedia;

    if (legacyGetUserMedia) {
        return new Promise((resolve, reject) => {
            legacyGetUserMedia.call(legacyNavigator, constraints, resolve, reject);
        });
    }

    throw new Error('Tu navegador no soporta captura de audio.');
};

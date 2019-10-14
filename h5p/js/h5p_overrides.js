H5P._getLibraryPath = H5P.getLibraryPath;
H5P.getLibraryPath = function (library) {
debugger;
    if (H5PIntegration.moodleLibraryPaths) {
        if (H5PIntegration.moodleLibraryPaths[library]) {
            return H5PIntegration.moodleLibraryPaths[library];
        }
    }
    return H5P._getLibraryPath(library);
};

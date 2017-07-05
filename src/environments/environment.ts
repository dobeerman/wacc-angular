// The file contents for the current environment will overwrite these during build.
// The build system defaults to the dev environment which uses `environment.ts`, but if you do
// `ng build --env=prod` then `environment.prod.ts` will be used instead.
// The list of which env maps to which file can be found in `.angular-cli.json`.

export const environment = {
    production: false,
    firebase : {
        apiKey: 'AIzaSyC3x1ZuU06MMrofcPG4W0l_Luoflk3lP1c',
        authDomain: 'wacc-bf98e.firebaseapp.com',
        databaseURL: 'https://wacc-bf98e.firebaseio.com',
        projectId: 'wacc-bf98e',
        storageBucket: 'wacc-bf98e.appspot.com',
        messagingSenderId: '971735087308'
    }
};

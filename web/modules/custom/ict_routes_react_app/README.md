# ICT Routes React App

### Developing the React app

 - From the project root, run `ddev ssh` to log into the `web` container
 - From inside the container run `cd web/modules/custom/ict_routes_react_app` to get to the React project root
 - You have the following commands:
   - `npm run build`: build the production dist into the `js/dist` folder 
   - `npm run build:dev`: build the development dist into the `js/dist_dev` folder 
   - `npm run start`: build the production dist into the `js/dist` folder, and watches for changes 
   - `npm run start:dev`: build the development dist into the `js/dist_dev` folder, and watches for changes

### How to fetch data

The container div to which the React app is binded will come with the URL to invoke to fetch data, stored in the 
`data-api-url` attribute. The response will be a json encoded payload. An example of payload is available at 
`js/data/sample.json` for any usage.

### Showing the app in Drupal

#### Pre-requisite: you need to have the Drupal site up and running. Please follow the root `README.md` for that

- From the root folder of the project, run `ddev drush uli` and click on the link to log as admin to the drupal site
- Create an empty "Basic page" in "Content > Add Content": just provide the title (i.e. "React app") and, scrolling all the way down the page, click on "Save and Publish" from the blue dropdown 
- Copy the url of the page (i.e. `/react-app`) and go to "Structure > Block"
- Find the "Content" section and click the contextual "Place block" button: in the modal, search for the "Real time bus data react app" block and choose it
- Under "Visibility > Pages", add the page url with the initial `/` (i.e. `/react-app`) and save the block
- Reload the page you created before, and you should see the React application
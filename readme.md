Sir Nibiru Project
This project is a PHP, Node.js, and JavaScript-based application with a MySQL database. It is designed to run on a XAMPP server and uses Webpack for bundling JavaScript files. This README will guide you through setting up the environment, installing dependencies, and running the project.

Prerequisites
Before you begin, ensure you have the following installed on your system:

XAMPP - A local server environment to run PHP and MySQL.
Download XAMPP and install it.
Node.js and npm - Node.js and npm are required to run the build scripts and manage dependencies.
Download Node.js (npm is included with Node.js).
Installation Guide
Step 1: Clone the Repository
Clone this repository into the XAMPP htdocs folder:

bash
Code kopieren
git clone git@github.com:Julius232/sir_nibiru.git
Move the project folder to the htdocs directory in XAMPP:

plaintext
Code kopieren
C:\xampp\htdocs\sir_nibiru
Step 2: Start XAMPP
Open XAMPP Control Panel.
Start the Apache and MySQL modules.
Step 3: Configure MySQL Database
Open phpMyAdmin by navigating to http://localhost/phpmyadmin in your browser.
Create a new database for the project (e.g., sir_nibiru_db).
Import any required SQL files for your database schema if available.
Step 4: Install Node.js Dependencies
Navigate to the node folder within your project directory and install the dependencies with npm:

bash
Code kopieren
cd C:\xampp\htdocs\sir_nibiru\node
npm install
This will install all dependencies specified in package.json, including both production and development dependencies.

Step 5: Run the Build
To bundle your JavaScript files with Webpack, run the following command from the node directory:

bash
Code kopieren
npm run build
This command will generate optimized JavaScript files in the build output directory (usually dist/).

Folder Structure
PHP Backend: The PHP files for the backend are in the root project directory (e.g., index.php, data.php, submit_signature.php).
Node and Webpack: All Node.js and Webpack configurations are located in the node folder.
src/script.js: The main JavaScript source file.
webpack.config.js: Webpack configuration file.
package.json: Lists dependencies and scripts for npm.
Running the Project
Once everything is set up:

Ensure that Apache and MySQL are running in XAMPP.
Open a browser and navigate to http://localhost/sir_nibiru to access the application.
Additional Information
npm: Make sure npm is in your PATH. You can verify by running npm -v in your command prompt.
MySQL Database Configuration: If your PHP code connects to the MySQL database, update the database credentials (host, username, password, and database name) in your PHP files.
Troubleshooting
If you encounter errors related to missing Node modules, ensure you’re in the node directory and run npm install.
Make sure XAMPP Apache and MySQL modules are running, and check that your MySQL database is correctly configured.
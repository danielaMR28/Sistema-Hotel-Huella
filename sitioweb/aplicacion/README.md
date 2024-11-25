# Medical Appointment System

This project is a simple web application to manage medical appointments for both patients and doctors. It allows users to create appointments, view appointment records, and manage them with basic CRUD operations.

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Contributing](#contributing)
- [License](#license)

## Installation

To set up the project, follow these steps:

1. **Download the Project**  
   Download and unzip the project file.
   
2. **Open the Project**  
   Open the unzipped folder in Visual Studio Code.

3. **Run Docker**  
   Open a terminal and run the following command to start the application:

   ```bash
   docker-compose up
   ```
4. **Verify Containers**  
   Ensure all three containers are running. You can verify this using Docker Desktop.

5. **Install mysqli**  
   Once the containers are running, open the terminal in the `php:apache` container from Docker Desktop by clicking the three dots. Run the following command:

   ```bash
   docker-php-ext-install mysqli
   ```
6. **Restart the Container**  
   After the installation is complete, click "Restart" in Docker Desktop for the `php:apache` container and wait for it to restart.

7. **Check Setup**  
   Ensure the containers have restarted successfully.

## Usage

Once the app is set up, follow these steps:

1. Open `localhost/login.php` in your browser.
2. Select the type of user:
   - **Patient**: You will be directed to a form to create a new appointment. Upon submission, you will receive a confirmation message.
   - **Doctor**: Log in using the credentials: `admin` / `admin`. You will see a list of all registered patients, ordered by appointment date.
   
   You can edit or delete any appointment from this section:
   - **Edit**: Modify the patient details.
   - **Delete**: A confirmation prompt will appear before deleting the record.

3. Use the "Back to Login" button to return to the user selection screen.

## Features

- **Patient Appointment Management**: Patients can create new appointments.
- **Doctor's Portal**: Doctors can view, edit, and delete appointments.
- **Login System**: Separate login for patients and doctors.

## Contributing

Feel free to contribute by submitting pull requests or reporting issues.

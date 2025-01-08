// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");
  
    signupForm.addEventListener("submit", (event) => {
      event.preventDefault(); // Prevent the form from submitting by default
  
      // Get form field values
      const email = document.getElementById("email").value.trim();
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      const phone = document.getElementById("phone").value.trim();
  
      // Validation flags
      let isValid = true;
      let errorMessage = "";
  
      // Check if all fields are filled
      if (!email || !username || !password || !phone) {
        isValid = false;
        errorMessage = "All fields are required.";
      }
  
      // Validate username (only letters and spaces, at least two names)
      if (
        isValid &&
        (!/^[A-Za-z\s]+$/.test(username) || username.trim().split(" ").length < 2)
      ) {
        isValid = false;
        errorMessage =
          "Username must only contain alphabetic characters and spaces, and must include your full name (e.g., 'Johnnie Walker').";
      }
  
      // Validate phone number
      if (isValid && (!/^(07\d{8})$/.test(phone) || phone.length !== 10)) {
        isValid = false;
        errorMessage = "Phone number must be exactly 10 digits and start with '07'.";
      }
  
      // Validate password strength
      if (
        isValid &&
        !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}/.test(password)
      ) {
        isValid = false;
        errorMessage =
          "Password must be at least 8 characters long, and include uppercase, lowercase, numbers, and special characters.";
      }
  
      // If validation fails, display an alert and stop
      if (!isValid) {
        alert(errorMessage);
        return;
      }
  
      // If all validations pass, display a welcome message
      alert(`Welcome, ${username.split(" ")[0]}! You have successfully signed up.`);
  
      // Redirect to the homepage
      window.location.href = "home.html";
    });
  });
  
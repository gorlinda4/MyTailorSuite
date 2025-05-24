<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TailorSuite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --error-color: #e74c3c;
            --success-color: #27ae60;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 30px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 40px;
            color: var(--primary-color);
        }
        
        .logo h1 {
            margin-top: 10px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .role-section {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: var(--error-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            color: var(--success-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function showRoleFields() {
            // Hide all role sections
            document.querySelectorAll('.role-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected role section
            const role = document.getElementById('role').value;
            if (role) {
                document.getElementById(`${role}-fields`).style.display = 'block';
            }
        }
        
        // Initialize role fields on page load
        document.addEventListener('DOMContentLoaded', showRoleFields);
    </script>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-cut"></i>
            <h1>TailorSuite</h1>
        </div>
        
        <form method="POST" action="register.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address">
                </div>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required onchange="showRoleFields()">
                    <option value="customer">Customer</option>
                    <option value="tailor">Tailor</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>
            
            <!-- Customer Fields -->
            <div id="customer-fields" class="role-section">
                <h3>Customer Details</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="chest">Chest (inches)</label>
                        <input type="number" id="chest" name="chest" min="20" max="60">
                    </div>
                    
                    <div class="form-group">
                        <label for="waist">Waist (inches)</label>
                        <input type="number" id="waist" name="waist" min="20" max="60">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hips">Hips (inches)</label>
                        <input type="number" id="hips" name="hips" min="20" max="60">
                    </div>
                    
                    <div class="form-group">
                        <label for="length">Length (inches)</label>
                        <input type="number" id="length" name="length" min="20" max="60">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="preferred_payment_method">Preferred Payment Method</label>
                    <select id="preferred_payment_method" name="preferred_payment_method">
                        <option value="mpesa">M-Pesa</option>
                        <option value="paypal">PayPal</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            
            <!-- Tailor Fields -->
            <div id="tailor-fields" class="role-section">
                <h3>Tailor Details</h3>
                
                <div class="form-group">
                    <label for="specialty">Specialty</label>
                    <input type="text" id="specialty" name="specialty">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="experience_years">Experience (years)</label>
                        <input type="number" id="experience_years" name="experience_years" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="hourly_rate">Hourly Rate</label>
                        <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="0.01">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="available">Available</option>
                        <option value="busy">Busy</option>
                        <option value="on_leave">On Leave</option>
                    </select>
                </div>
            </div>
            
            <!-- Manager Fields -->
            <div id="manager-fields" class="role-section">
                <h3>Manager Details</h3>
                
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department">
                </div>
                
                <div class="form-group">
                    <label for="access_level">Access Level</label>
                    <select id="access_level" name="access_level">
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3 (Admin)</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
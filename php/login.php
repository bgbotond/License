<html>
	<head>
		<title>License Server Login</title>
	</head>
	<body>
		<div align="center" style="margin: 100">
			<h3>Login</h3>
			<FORM NAME ="loginForm" METHOD ="POST" ACTION ="license/admin.php">
				<div>
					Username
					<INPUT type = 'text' name ='username' value="" maxlength="20">
				</div>
				<div>
					Password
					<INPUT type = 'password' name ='password' value="" maxlength="16">
				</div>
				<P>
				<INPUT type = "Submit" name = "login" value = "Login">
			</FORM>
		</div>
	</body>
</html>
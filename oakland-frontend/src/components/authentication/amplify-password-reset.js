import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import NextLink from 'next/link';
import * as Yup from 'yup';
import { useFormik } from 'formik';
import { Box, Button, FormHelperText, Grid, Typography } from '@mui/material';
import { useAuth } from '../../hooks/use-auth';
import { useMounted } from '../../hooks/use-mounted';
import { InputField } from '../input-field';

export const AmplifyPasswordReset = (props) => {
  const isMounted = useMounted();
  const { passwordReset } = useAuth();
  const router = useRouter();
  const [username, setUsername] = useState('');
  const formik = useFormik({
    enableReinitialize: true,
    initialValues: {
      code: '',
      email: username,
      password: '',
      passwordConfirm: '',
      submit: null
    },
    validationSchema: Yup.object({
      code: Yup
        .string()
        .required('Code is required'),
      email: Yup
        .string()
        .email('Must be a valid email')
        .max(255)
        .required('Email is required'),
      password: Yup
        .string()
        .min(7, 'Must be at least 7 characters')
        .max(255)
        .required('Required'),
      passwordConfirm: Yup
        .string()
        .oneOf([Yup.ref('password'), null], 'Passwords must match')
        .required('Required')
    }),
    onSubmit: async (values, helpers) => {
      try {
        await passwordReset(values.email, values.code, values.password);

        if (isMounted()) {
          router.push('/authentication/login').catch(console.error);
        }
      } catch (err) {
        console.error(err);

        if (isMounted()) {
          helpers.setStatus({ success: false });
          helpers.setErrors({ submit: err.message });
          helpers.setSubmitting(false);
        }
      }
    }
  });

  useEffect(() => {
    const storedUsername = sessionStorage.getItem('username');

    if (storedUsername) {
      setUsername(storedUsername);
    }
  }, []);

  return (
    <form
      noValidate
      onSubmit={formik.handleSubmit}
      {...props}>
      <Box
        sx={{
          alignItems: 'center',
          display: 'flex',
          mb: 3
        }}
      >
        <Typography
          color="textPrimary"
          variant="h4"
        >
          Reset Password
        </Typography>
        <Box sx={{ flexGrow: 1 }} />
        <NextLink
          href="/authentication/login"
          passHref
        >
          <Button
            color="primary"
            component="a"
            variant="text"
          >
            Sign in
          </Button>
        </NextLink>
      </Box>
      <Grid
        container
        spacing={2}
      >
        <Grid
          item
          xs={12}
        >
          <InputField
            autoFocus
            disabled={!!username}
            error={Boolean(formik.touched.email && formik.errors.email)}
            fullWidth
            helperText={formik.touched.email && formik.errors.email}
            label="Email Address"
            name="email"
            onBlur={formik.handleBlur}
            onChange={formik.handleChange}
            type="email"
            value={formik.values.email}
          />
        </Grid>
        <Grid
          item
          xs={12}
        >
          <InputField
            autoFocus={!!username}
            error={Boolean(formik.touched.code && formik.errors.code)}
            fullWidth
            helperText={formik.touched.code && formik.errors.code}
            label="Reset code"
            name="code"
            onBlur={formik.handleBlur}
            onChange={formik.handleChange}
            value={formik.values.code}
          />
        </Grid>
        <Grid
          item
          xs={12}
        >
          <InputField
            error={Boolean(formik.touched.password && formik.errors.password)}
            fullWidth
            helperText={formik.touched.password && formik.errors.password}
            label="Password"
            name="password"
            onBlur={formik.handleBlur}
            onChange={formik.handleChange}
            type="password"
            value={formik.values.password}
          />
        </Grid>
        <Grid
          item
          xs={12}
        >
          <InputField
            error={Boolean(formik.touched.passwordConfirm
              && formik.errors.passwordConfirm)}
            fullWidth
            helperText={formik.touched.passwordConfirm
              && formik.errors.passwordConfirm}
            label="Password Confirmation"
            name="passwordConfirm"
            onBlur={formik.handleBlur}
            onChange={formik.handleChange}
            type="password"
            value={formik.values.passwordConfirm}
          />
        </Grid>
        {formik.errors.submit && (
          <Grid
            item
            xs={12}
          >
            <FormHelperText error>
              {formik.errors.submit}
            </FormHelperText>
          </Grid>
        )}
        <Grid
          item
          xs={12}
        >
          <Button
            color="primary"
            disabled={formik.isSubmitting}
            fullWidth
            size="large"
            type="submit"
            variant="contained"
          >
            Reset password
          </Button>
        </Grid>
      </Grid>
    </form>
  );
};

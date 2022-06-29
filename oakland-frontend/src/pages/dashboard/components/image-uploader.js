import { useEffect } from 'react';
import Head from 'next/head';
import { Box, Container, Typography } from '@mui/material';
import { AuthGuard } from '../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../components/dashboard/dashboard-layout';
import { ImagesUploader } from '../../../components/images-uploader';
import { gtm } from '../../../lib/gtm';

const ComponentsImageUploader = () => {
  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  return (
    <>
      <Head>
        <title>
          Components: Image Uploader| Carpatin Dashboard
        </title>
      </Head>
      <Box
        sx={{
          flexGrow: 1,
          py: 4
        }}
      >
        <Container maxWidth="xl">
          <Typography
            color="textPrimary"
            sx={{ mb: 6 }}
            variant="h4"
          >
            Image Uploader
          </Typography>
          <div>
            <Typography
              color="textPrimary"
              sx={{ mb: 2 }}
              variant="body1"
            >
              Multiple image selector. Click the Browse button below.
            </Typography>
            <ImagesUploader />
          </div>
        </Container>
      </Box>
    </>
  );
};

ComponentsImageUploader.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default ComponentsImageUploader;

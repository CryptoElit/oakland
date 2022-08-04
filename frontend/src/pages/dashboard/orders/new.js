import { useEffect } from 'react';
import NextLink from 'next/link';
import Head from 'next/head';
import { Box, Breadcrumbs, Container, Grid, Link, Typography } from '@mui/material';
import { AuthGuard } from '../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../components/dashboard/dashboard-layout';
import { OrderCreateForm } from '../../../components/dashboard/order/order-create-form';
import { gtm } from '../../../lib/gtm';

const OrderCreate = () => {
  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  return (
    <>
      <Head>
        <title>
          New Order | Oakland
        </title>
      </Head>
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          py: 8
        }}
      >
        <Container maxWidth="md">
          <Box sx={{ mb: 3 }}>
            <Grid 
              container
              spacing={3}
            >
              <Grid item
              md={10}
              xs={12}
              >
              <Typography variant="h4">
              New Order
            </Typography>
            
              </Grid>
              <Grid item
                md={1}
              xs={12}
              >
                <Typography variant="h4">
              #000019
            </Typography>
              </Grid>

            </Grid>
            
          </Box>
          <OrderCreateForm />
        </Container>
      </Box>
    </>
  );
};

OrderCreate.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default OrderCreate;

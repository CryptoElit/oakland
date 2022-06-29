import { useEffect } from 'react';
import Head from 'next/head';
import { Box, Card, CardContent, Grid, Typography } from '@mui/material';
import { AuthGuard } from '../../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../../components/dashboard/dashboard-layout';
import { ProductLayout } from '../../../../components/dashboard/product/product-layout';
import { ProductChannel } from '../../../../components/dashboard/product/product-channel';
import { ProductReturnRate } from '../../../../components/dashboard/product/product-return-rate';
import { ProductReviews } from '../../../../components/dashboard/product/product-reviews';
import { ProductSalesReport } from '../../../../components/dashboard/product/product-sales-report';
import { gtm } from '../../../../lib/gtm';

const ProductAnalytics = () => {
  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  return (
    <>
      <Head>
        <title>
          Product: Analytics | Carpatin Dashboard
        </title>
      </Head>
      <Box sx={{ flexGrow: 1 }}>
        <Grid
          container
          spacing={3}
        >
          <Grid
            container
            item
            md={4}
            spacing={3}
            sx={{ height: 'fit-content' }}
            xs={12}
          >
            <Grid
              item
              xs={12}
            >
              <Typography
                color="textPrimary"
                variant="h6"
              >
                All time
              </Typography>
            </Grid>
            <Grid
              item
              xs={12}
            >
              <Card>
                <CardContent>
                  <Typography
                    color="textSecondary"
                    variant="subtitle2"
                  >
                    Monthly Recurring Revenue
                  </Typography>
                  <Typography
                    color="textPrimary"
                    variant="h4"
                  >
                    € 3,200.00
                  </Typography>
                </CardContent>
              </Card>
            </Grid>
            <Grid
              item
              xs={12}
            >
              <Card>
                <CardContent>
                  <Typography
                    color="textSecondary"
                    variant="subtitle2"
                  >
                    Order count for this product
                  </Typography>
                  <Typography
                    color="textPrimary"
                    variant="h4"
                  >
                    356
                  </Typography>
                </CardContent>
              </Card>
            </Grid>
            <Grid
              item
              xs={12}
            >
              <ProductReviews />
            </Grid>
          </Grid>
          <Grid
            container
            item
            md={8}
            spacing={3}
            sx={{ height: 'fit-content' }}
            xs={12}
          >
            <Grid
              item
              xs={12}
            >
              <Typography
                color="textPrimary"
                variant="h6"
              >
                Last 30 days
              </Typography>
            </Grid>
            <Grid
              item
              xs={12}
            >
              <ProductSalesReport />
            </Grid>
            <Grid
              item
              md={6}
              xs={12}
            >
              <ProductChannel />
            </Grid>
            <Grid
              item
              md={6}
              xs={12}
            >
              <ProductReturnRate />
            </Grid>
          </Grid>
        </Grid>
      </Box>
    </>
  );
};

ProductAnalytics.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      <ProductLayout>
        {page}
      </ProductLayout>
    </DashboardLayout>
  </AuthGuard>
);

export default ProductAnalytics;

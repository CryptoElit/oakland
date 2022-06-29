import { useCallback, useEffect, useState } from 'react';
import Head from 'next/head';
import { Box, Grid } from '@mui/material';
import { productApi } from '../../../../api/product';
import { AuthGuard } from '../../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../../components/dashboard/dashboard-layout';
import { ProductLayout } from '../../../../components/dashboard/product/product-layout';
import { ProductInfo } from '../../../../components/dashboard/product/product-info';
import { ProductInfoDialog } from '../../../../components/dashboard/product/product-info-dialog';
import { ProductStatus } from '../../../../components/dashboard/product/product-status';
import { ProductVariants } from '../../../../components/dashboard/product/product-variants';
import { ResourceError } from '../../../../components/resource-error';
import { ResourceLoading } from '../../../../components/resource-loading';
import { useMounted } from '../../../../hooks/use-mounted';
import { gtm } from '../../../../lib/gtm';

const ProductSummary = () => {
  const isMounted = useMounted();
  const [productState, setProductState] = useState({ isLoading: true });
  const [openInfoDialog, setOpenInfoDialog] = useState(false);

  const getProduct = useCallback(async () => {
    setProductState(() => ({ isLoading: true }));

    try {
      const result = await productApi.getProduct();

      if (isMounted()) {
        setProductState(() => ({
          isLoading: false,
          data: result
        }));
      }
    } catch (err) {
      console.error(err);

      if (isMounted()) {
        setProductState(() => ({
          isLoading: false,
          error: err.message
        }));
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    getProduct().catch(console.error);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  const renderContent = () => {
    if (productState.isLoading) {
      return <ResourceLoading />;
    }

    if (productState.error) {
      return <ResourceError />;
    }

    return (
      <>
        <Grid
          container
          spacing={3}
        >
          <Grid
            container
            item
            lg={8}
            spacing={3}
            sx={{
              height: 'fit-content',
              order: {
                md: 2,
                xs: 1
              }
            }}
            xs={12}
          >
            <Grid
              item
              xs={12}
            >
              <ProductInfo
                onEdit={() => setOpenInfoDialog(true)}
                product={productState.data}
              />
            </Grid>
            <Grid
              item
              xs={12}
            >
              <ProductVariants variants={productState.data?.variants} />
            </Grid>
          </Grid>
          <Grid
            container
            item
            lg={4}
            spacing={3}
            sx={{
              height: 'fit-content',
              order: {
                md: 2,
                xs: 1
              }
            }}
            xs={12}
          >
            <Grid
              item
              xs={12}
            >
              <ProductStatus product={productState.data} />
            </Grid>
          </Grid>
        </Grid>
        <ProductInfoDialog
          onClose={() => setOpenInfoDialog(false)}
          open={openInfoDialog}
          product={productState.data}
        />
      </>
    );
  };

  return (
    <>
      <Head>
        <title>
          Product: Summary | Carpatin Dashboard
        </title>
      </Head>
      <Box sx={{ flexGrow: 1 }}>
        {renderContent()}
      </Box>
    </>
  );
};

ProductSummary.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      <ProductLayout>
        {page}
      </ProductLayout>
    </DashboardLayout>
  </AuthGuard>
);

export default ProductSummary;

import { useCallback, useEffect, useState } from 'react';
import NextLink from 'next/link';
import Head from 'next/head';
import { PDFDownloadLink } from '@react-pdf/renderer';
import { Box, Button, Container, Skeleton, Typography } from '@mui/material';
import { invoiceApi } from '../../../../api/invoice';
import { AuthGuard } from '../../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../../components/dashboard/dashboard-layout';
import { InvoicePdfPreview } from '../../../../components/dashboard/invoices/invoice-pdf-preview';
import { InvoicePDF } from '../../../../components/dashboard/invoices/invoice-pdf';
import { useMounted } from '../../../../hooks/use-mounted';
import { ArrowLeft as ArrowLeftIcon } from '../../../../icons/arrow-left';
import { Download as DownloadIcon } from '../../../../icons/download';
import { ExclamationOutlined as ExclamationOutlinedIcon } from '../../../../icons/exclamation-outlined';
import { gtm } from '../../../../lib/gtm';

const InvoicePreview = () => {
  const isMounted = useMounted();
  const [invoiceState, setInvoiceState] = useState({ isLoading: true });

  const getInvoice = useCallback(async () => {
    setInvoiceState(() => ({ isLoading: true }));

    try {
      const result = await invoiceApi.getInvoice();

      if (isMounted()) {
        setInvoiceState(() => ({
          isLoading: false,
          data: result
        }));
      }
    } catch (err) {
      console.error(err);

      if (isMounted()) {
        setInvoiceState(() => ({
          isLoading: false,
          error: err.message
        }));
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    getInvoice().catch(console.error);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  const renderContent = () => {
    if (invoiceState.isLoading) {
      return (
        <Box sx={{ py: 4 }}>
          <Skeleton height={42} />
          <Skeleton />
          <Skeleton />
        </Box>
      );
    }

    if (invoiceState.error) {
      return (
        <Box sx={{ py: 4 }}>
          <Box
            sx={{
              alignItems: 'center',
              backgroundColor: 'background.default',
              display: 'flex',
              flexDirection: 'column',
              p: 3
            }}
          >
            <ExclamationOutlinedIcon />
            <Typography
              color="textSecondary"
              sx={{ mt: 2 }}
              variant="body2"
            >
              {invoiceState.error}
            </Typography>
          </Box>
        </Box>
      );
    }

    return (
      <>
        <Box sx={{ py: 4 }}>
          <Box sx={{ mb: 2 }}>
            <NextLink
              href="/dashboard/invoices"
              passHref
            >
              <Button
                color="primary"
                component="a"
                startIcon={<ArrowLeftIcon />}
                variant="text"
              >
                Invoices
              </Button>
            </NextLink>
          </Box>
          <Box
            sx={{
              alignItems: 'center',
              display: 'flex'
            }}
          >
            <Typography
              color="textPrimary"
              variant="h4"
            >
              Invoice Preview
            </Typography>
            <Box sx={{ flexGrow: 1 }} />
            <PDFDownloadLink
              document={<InvoicePDF invoice={invoiceState.data} />}
              fileName="invoice"
              style={{ textDecoration: 'none' }}
            >
              <Button
                color="primary"
                startIcon={<DownloadIcon />}
                size="large"
                variant="contained"
              >
                Download
              </Button>
            </PDFDownloadLink>
          </Box>
        </Box>
        <InvoicePdfPreview invoice={invoiceState.data} />
      </>
    );
  };

  return (
    <>
      <Head>
        <title>Invoice: Preview | Carpatin Dashboard</title>
      </Head>
      <Box
        sx={{
          backgroundColor: 'background.default',
          flexGrow: 1
        }}
      >
        <Container
          maxWidth="lg"
          sx={{
            display: 'flex',
            flexDirection: 'column',
            height: '100%'
          }}
        >
          {renderContent()}
        </Container>
      </Box>
    </>
  );
};

InvoicePreview.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default InvoicePreview;

import { useCallback, useEffect, useState } from 'react';
import NextLink from 'next/link';
import Head from 'next/head';
import { Box, Button, Card, Container, Divider, Typography } from '@mui/material';
import { invoiceApi } from '../../../api/invoice';
import { AuthGuard } from '../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../components/dashboard/dashboard-layout';
import { InvoicesFilter } from '../../../components/dashboard/invoices/invoices-filter';
import { InvoicesStats } from '../../../components/dashboard/invoices/invoices-stats';
import { InvoicesTable } from '../../../components/dashboard/invoices/invoices-table';
import { useMounted } from '../../../hooks/use-mounted';
import { useSelection } from '../../../hooks/use-selection';
import { Plus as PlusIcon } from '../../../icons/plus';
import { gtm } from '../../../lib/gtm';

const Invoices = () => {
  const isMounted = useMounted();
  const [controller, setController] = useState({
    filters: [],
    page: 0,
    query: '',
    sort: 'desc',
    sortBy: 'createdAt',
    view: 'all'
  });
  const [invoicesState, setInvoicesState] = useState({ isLoading: true });
  const [selectedInvoices, handleSelect, handleSelectAll] = useSelection(invoicesState.data?.invoices);

  const getCustomers = useCallback(async () => {
    setInvoicesState(() => ({ isLoading: true }));

    try {
      const result = await invoiceApi.getInvoices({
        filters: controller.filters,
        page: controller.page,
        query: controller.query,
        sort: controller.sort,
        sortBy: controller.sortBy,
        view: controller.view
      });

      if (isMounted()) {
        setInvoicesState(() => ({
          isLoading: false,
          data: result
        }));
      }
    } catch (err) {
      console.error(err);

      if (isMounted()) {
        setInvoicesState(() => ({
          isLoading: false,
          error: err.message
        }));
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [controller]);

  useEffect(() => {
    getCustomers().catch(console.error);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [controller]);

  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  const handleViewChange = (newView) => {
    setController({
      ...controller,
      page: 0,
      view: newView
    });
  };

  const handleQueryChange = (newQuery) => {
    setController({
      ...controller,
      page: 0,
      query: newQuery
    });
  };

  const handleFiltersApply = (newFilters) => {
    const parsedFilters = newFilters.map((filter) => ({
      property: filter.property.name,
      value: filter.value,
      operator: filter.operator.value
    }));

    setController({
      ...controller,
      page: 0,
      filters: parsedFilters
    });
  };

  const handleFiltersClear = () => {
    setController({
      ...controller,
      page: 0,
      filters: []
    });
  };

  const handlePageChange = (newPage) => {
    setController({
      ...controller,
      page: newPage - 1
    });
  };

  const handleSortChange = (event, property) => {
    const isAsc = controller.sortBy === property && controller.sort === 'asc';

    setController({
      ...controller,
      page: 0,
      sort: isAsc ? 'desc' : 'asc',
      sortBy: property
    });
  };

  return (
    <>
      <Head>
        <title>
          Invoice: List | Carpatin Dashboard
        </title>
      </Head>
      <Box
        sx={{
          backgroundColor: 'background.default',
          flexGrow: 1
        }}
      >
        <Container
          maxWidth="xl"
          sx={{
            display: 'flex',
            flexDirection: 'column',
            height: '100%'
          }}
        >
          <Box sx={{ py: 4 }}>
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
                Invoices
              </Typography>
              <Box sx={{ flexGrow: 1 }} />
              <NextLink
                href="/dashboard/invoices/create"
                passHref
              >
                <Button
                  color="primary"
                  component="a"
                  size="large"
                  startIcon={<PlusIcon fontSize="small" />}
                  variant="contained"
                >
                  Add
                </Button>
              </NextLink>
            </Box>
          </Box>
          <InvoicesStats />
          <Card
            sx={{
              display: 'flex',
              flexDirection: 'column',
              flexGrow: 1
            }}
          >
            <InvoicesFilter
              disabled={invoicesState.isLoading}
              filters={controller.filters}
              onFiltersApply={handleFiltersApply}
              onFiltersClear={handleFiltersClear}
              onQueryChange={handleQueryChange}
              onViewChange={handleViewChange}
              query={controller.query}
              selectedInvoices={selectedInvoices}
              view={controller.view}
            />
            <Divider />
            <InvoicesTable
              error={invoicesState.error}
              invoices={invoicesState.data?.invoices}
              invoicesCount={invoicesState.data?.invoicesCount}
              isLoading={invoicesState.isLoading}
              onPageChange={handlePageChange}
              onSelect={handleSelect}
              onSelectAll={handleSelectAll}
              onSortChange={handleSortChange}
              page={controller.page + 1}
              selectedInvoices={selectedInvoices}
              sort={controller.sort}
              sortBy={controller.sortBy}
            />
          </Card>
        </Container>
      </Box>
    </>
  );
};

Invoices.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default Invoices;

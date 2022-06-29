import { useCallback, useEffect, useState } from 'react';
import Head from 'next/head';
import { Box, Button, Card, Container, Divider, Typography } from '@mui/material';
import { productApi } from '../../../api/product';
import { AuthGuard } from '../../../components/authentication/auth-guard';
import { DashboardLayout } from '../../../components/dashboard/dashboard-layout';
import { ProductCreateDialog } from '../../../components/dashboard/product/product-create-dialog';
import { ProductsFilter } from '../../../components/dashboard/product/products-filter';
import { ProductsSummary } from '../../../components/dashboard/product/products-summary';
import { ProductsTable } from '../../../components/dashboard/product/products-table';
import { useMounted } from '../../../hooks/use-mounted';
import { useSelection } from '../../../hooks/use-selection';
import { Plus as PlusIcon } from '../../../icons/plus';
import { gtm } from '../../../lib/gtm';

const Products = () => {
  const isMounted = useMounted();
  const [controller, setController] = useState({
    filters: [],
    page: 0,
    query: '',
    sort: 'desc',
    sortBy: 'updatedAt',
    view: 'all'
  });
  const [productsState, setProductsState] = useState({ isLoading: true });
  const [selectedProducts, handleSelect, handleSelectAll] = useSelection(productsState.data?.products);
  const [openCreateDialog, setOpenCreateDialog] = useState(false);

  const getProducts = useCallback(async () => {
    setProductsState(() => ({ isLoading: true }));

    try {
      const result = await productApi.getProducts({
        filters: controller.filters,
        page: controller.page,
        query: controller.query,
        sort: controller.sort,
        sortBy: controller.sortBy,
        view: controller.view
      });

      if (isMounted()) {
        setProductsState(() => ({
          isLoading: false,
          data: result
        }));
      }
    } catch (err) {
      console.error(err);

      if (isMounted()) {
        setProductsState(() => ({
          isLoading: false,
          error: err.message
        }));
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [controller]);

  useEffect(() => {
    getProducts().catch(console.error);
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
        <title>Product: List | Carpatin Dashboard</title>
      </Head>
      <Box sx={{ flexGrow: 1 }}>
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
                Products
              </Typography>
              <Box sx={{ flexGrow: 1 }} />
              <Button
                color="primary"
                onClick={() => setOpenCreateDialog(true)}
                size="large"
                startIcon={<PlusIcon fontSize="small" />}
                variant="contained"
              >
                Add
              </Button>
            </Box>
          </Box>
          <ProductsSummary />
          <Card
            sx={{
              display: 'flex',
              flexDirection: 'column',
              flexGrow: 1
            }}
          >
            <ProductsFilter
              disabled={productsState.isLoading}
              filters={controller.filters}
              onFiltersApply={handleFiltersApply}
              onFiltersClear={handleFiltersClear}
              onQueryChange={handleQueryChange}
              onViewChange={handleViewChange}
              query={controller.query}
              selectedProducts={selectedProducts}
              view={controller.view}
            />
            <Divider />
            <ProductsTable
              error={productsState.error}
              isLoading={productsState.isLoading}
              onPageChange={handlePageChange}
              onSelect={handleSelect}
              onSelectAll={handleSelectAll}
              onSortChange={handleSortChange}
              page={controller.page + 1}
              products={productsState.data?.products}
              productsCount={productsState.data?.productsCount}
              selectedProducts={selectedProducts}
              sort={controller.sort}
              sortBy={controller.sortBy}
            />
          </Card>
        </Container>
      </Box>
      <ProductCreateDialog
        onClose={() => setOpenCreateDialog(false)}
        open={openCreateDialog}
      />
    </>
  );
};

Products.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default Products;

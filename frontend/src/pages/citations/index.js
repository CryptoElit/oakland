import {useCallback, useEffect, useRef, useState} from 'react';
import Head from 'next/head';
import {Box, Button, Divider, Grid, InputAdornment, Tab, Tabs, TextField, Typography} from '@mui/material';
import {styled} from '@mui/material/styles';
import {orderApi} from '../../api/order-api';
import {AuthGuard} from '../../components/authentication/auth-guard';
import {DashboardLayout} from '../../components/dashboard/dashboard-layout';
import {CitationDrawer} from '../../components/dashboard/citation/citation-drawer';
import {CitationListTable} from '../../components/dashboard/citation/citation-list-table';
import {useMounted} from '../../hooks/use-mounted';
import {Plus as PlusIcon} from '../../icons/plus';
import {Search as SearchIcon} from '../../icons/search';
import {gtm} from '../../lib/gtm';
import {DataGridHelper} from '../../components/data-grid-helper';


const tabs = [
  {
    label: 'All',
    value: 'all'
  },
  {
    label: 'Canceled',
    value: 'canceled'
  },
  {
    label: 'Completed',
    value: 'complete'
  },
  {
    label: 'Pending',
    value: 'pending'
  },
  {
    label: 'Rejected',
    value: 'rejected'
  }
];

const sortOptions = [
  {
    label: 'Newest',
    value: 'desc'
  },
  {
    label: 'Oldest',
    value: 'asc'
  }
];

const applyFilters = (orders, filters) => orders.filter((order) => {
  if (filters.query) {
    // Checks only the order number, but can be extended to support other fields, such as user
    // name, email, etc.
    const containsQuery = (order.number || '').toLowerCase().includes(filters.query.toLowerCase());

    if (!containsQuery) {
      return false;
    }
  }

  if (typeof filters.status !== 'undefined') {
    const statusMatched = order.status === filters.status;

    if (!statusMatched) {
      return false;
    }
  }

  return true;
});

const applySort = (orders, sortDir) => orders.sort((a, b) => {
  const comparator = a.createdAt > b.createdAt ? -1 : 1;

  return sortDir === 'desc' ? comparator : -comparator;
});

const applyPagination = (orders, page, rowsPerPage) => orders.slice(page * rowsPerPage,
  page * rowsPerPage + rowsPerPage);

const CitationListInner = styled('div',
  { shouldForwardProp: (prop) => prop !== 'open' })(
  ({ theme, open }) => ({
    flexGrow: 1,
    overflow: 'hidden',
    paddingBottom: theme.spacing(8),
    paddingTop: theme.spacing(8),
    zIndex: 1,
    [theme.breakpoints.up('lg')]: {
      marginRight: -500
    },
    transition: theme.transitions.create('margin', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen
    }),
    ...(open && {
      [theme.breakpoints.up('lg')]: {
        marginRight: 0
      },
      transition: theme.transitions.create('margin', {
        easing: theme.transitions.easing.easeOut,
        duration: theme.transitions.duration.enteringScreen
      })
    })
  }));

const CitationList = () => {
  const isMounted = useMounted();
  const rootRef = useRef(null);
  const queryRef = useRef(null);
  const [currentTab, setCurrentTab] = useState('all');
  const [sort, setSort] = useState('desc');
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const [orders, setCitations] = useState([]);
  const [filters, setFilters] = useState({
    query: '',
    status: undefined
  });
  const [drawer, setDrawer] = useState({
    isOpen: false,
    orderId: undefined
  });

  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  const getCitations = useCallback(async () => {
    try {
      const data = await orderApi.getCitations();

      if (isMounted()) {
        setCitations(data);
      }
    } catch (err) {
      console.error(err);
    }
  }, [isMounted]);

  useEffect(() => {
      getCitations();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []);

  const handleTabsChange = (event, value) => {
    setCurrentTab(value);
    setFilters((prevState) => ({
      ...prevState,
      status: value === 'all' ? undefined : value
    }));
  };

  const handleQueryChange = (event) => {
    event.preventDefault();
    setFilters((prevState) => ({
      ...prevState,
      query: queryRef.current?.value
    }));
  };

  const handleSortChange = (event) => {
    const value = event.target.value;
    setSort(value);
  };

  const handlePageChange = (event, newPage) => {
    setPage(newPage);
  };

  const handleRowsPerPageChange = (event) => {
    setRowsPerPage(parseInt(event.target.value, 10));
  };

  const handleOpenDrawer = (orderId) => {
    setDrawer({
      isOpen: true,
      orderId
    });
  };

  const handleCloseDrawer = () => {
    setDrawer({
      isOpen: false,
      orderId: undefined
    });
  };

  // Usually query is done on backend with indexing solutions
  const filteredCitations = applyFilters(orders, filters);
  const sortedCitations = applySort(filteredCitations, sort);
  const paginatedCitations = applyPagination(sortedCitations, page, rowsPerPage);

  return (
    <>
      <Head>
        <title>
          Dashboard: Citation List |
        </title>
      </Head>
      <Box
        component="main"
        ref={rootRef}
        sx={{
          backgroundColor: 'background.paper',
          display: 'flex',
          flexGrow: 1,
          overflow: 'hidden'
        }}
      >
        <CitationListInner open={drawer.isOpen}>
          <Box sx={{ px: 3 }}>
            <Grid
              container
              justifyContent="space-between"
              spacing={3}
            >
              <Grid item>
                <Typography variant="h4">
                  Citations
                </Typography>
              </Grid>
              <Grid item>
                <Button
                  startIcon={<PlusIcon fontSize="small" />}
                  variant="contained"
                >
                  Add
                </Button>
              </Grid>
            </Grid>
            <DataGridHelper />
          </Box>
          <Divider />
          <Box
            sx={{
              alignItems: 'center',
              display: 'flex',
              flexWrap: 'wrap',
              m: -1.5,
              p: 3
            }}
          >
            <Box
              component="form"
              onSubmit={handleQueryChange}
              sx={{
                flexGrow: 1,
                m: 1.5
              }}
            >
              <TextField
                defaultValue=""
                fullWidth
                inputProps={{ ref: queryRef }}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <SearchIcon fontSize="small" />
                    </InputAdornment>
                  )
                }}
                placeholder="Search by order number"
              />
            </Box>
            <TextField
              label="Sort By"
              name="order"
              onChange={handleSortChange}
              select
              SelectProps={{ native: true }}
              sx={{ m: 1.5 }}
              value={sort}
            >
              {sortOptions.map((option) => (
                <option
                  key={option.value}
                  value={option.value}
                >
                  {option.label}
                </option>
              ))}
            </TextField>
          </Box>
          <Divider />
          <CitationListTable
            onOpenDrawer={handleOpenDrawer}
            onPageChange={handlePageChange}
            onRowsPerPageChange={handleRowsPerPageChange}
            orders={paginatedCitations}
            ordersCount={filteredCitations.length}
            page={page}
            rowsPerPage={rowsPerPage}
          />
        </CitationListInner>
        <CitationDrawer
          containerRef={rootRef}
          onClose={handleCloseDrawer}
          open={drawer.isOpen}
          order={orders.find((order) => order.id === drawer.orderId)}
        />
      </Box>
    </>
  );
};

CitationList.getLayout = (page) => (
  <AuthGuard>
    <DashboardLayout>
      {page}
    </DashboardLayout>
  </AuthGuard>
);

export default CitationList;

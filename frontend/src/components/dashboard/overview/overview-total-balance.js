/*
import numeral from 'numeral';
import {
  Box,
  Button,
  Card,
  CardContent,
  Divider,
  List,
  ListItem,
  ListItemText,
  Typography
} from '@mui/material';
import { ArrowRight as ArrowRightIcon } from '../../../icons/arrow-right';

const currencies = [
  {
    amount: 21500,
    color: '#2F3EB1',
    name: 'Maintenance'
  },
  {
    amount: 15300,
    color: '#0C7CD5',
    name: 'Logistics'
  },
  {
    amount: 1076.81,
    color: '#7BC67E',
    name: 'Machinery'
  }
];

export const OverviewTotalBalance = (props) => (
  <Card {...props}>
    <CardContent>
      <Typography
        color="textSecondary"
        variant="overline"
      >
        Live Budget
      </Typography>
      <Typography variant="h4">
        R {numeral(1178394).format('0,0.00')}
      </Typography>

      <Divider sx={{ my: 2 }} />
      <Typography
        color="textSecondary"
        variant="overline"
      >
        Department Budgets
      </Typography>
      <List
        disablePadding
        sx={{ pt: 2 }}
      >
        {currencies.map((currency) => (
          <ListItem
            disableGutters
            key={currency.name}
            sx={{
              pb: 2,
              pt: 0
            }}
          >
            <ListItemText
              disableTypography
              primary={(
                <Box
                  sx={{
                    alignItems: 'center',
                    display: 'flex',
                    justifyContent: 'space-between'
                  }}
                >
                  <Box
                    sx={{
                      alignItems: 'center',
                      display: 'flex'
                    }}
                  >
                    <Box
                      sx={{
                        border: 3,
                        borderColor: currency.color,
                        borderRadius: '50%',
                        height: 16,
                        mr: 1,
                        width: 16
                      }}
                    />
                    <Typography variant="subtitle2">
                      {currency.name}
                    </Typography>
                  </Box>
                  <Typography
                    color="textSecondary"
                    variant="subtitle2"
                  >
                    {numeral(currency.amount).format('$0,0.00')}
                  </Typography>
                </Box>
              )}
            />
          </ListItem>
        ))}
      </List>
      <Divider />
      <Box
        sx={{
          alignItems: 'flex-start',
          display: 'flex',
          flexDirection: 'column',
          pt: 2
        }}
      >
        <Button endIcon={<ArrowRightIcon fontSize="small" />}>
          Add money
        </Button>
        <Button
          endIcon={<ArrowRightIcon fontSize="small" />}
          sx={{ mt: 2 }}
        >
          Withdraw funds
        </Button>
      </Box>
    </CardContent>
  </Card>
);
*/
import { format, subDays } from 'date-fns';
import { Avatar, Box, Card, Container, Typography } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { Refresh as RefreshIcon } from '../../../icons/refresh';
import { Chart } from '../../chart';

const getCategories = () => {
  const now = new Date();
  const categories = [];

  for (let i = 6; i >= 0; i--) {
    categories.push(format(subDays(now, i), 'dd/MM/yyyy'));
  }

  return categories;
};

export const OverviewTotalBalance = (props) => {
  const theme = useTheme();

  const chartOptions = {
    chart: {
      background: 'transparent',
      toolbar: {
        show: false
      }
    },
    dataLabels: {
      enabled: false
    },
    fill: {
      opacity: 1
    },
    grid: {
      yaxis: {
        lines: {
          show: false
        }
      },
      xaxis: {
        lines: {
          show: false
        }
      }
    },
    legend: {
      show: false
    },
    stroke: {
      width: 2,
      colors: ['#f44336']
    },
    theme: {
      mode: theme.palette.mode
    },
    xaxis: {
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      },
      categories: getCategories(),
      labels: {
        show: false
      }
    },
    yaxis: {
      labels: {
        show: false
      }
    }
  };

  const chartSeries = [
    {
      data: [14, 43, 98, 68, 155, 18, 8],
      name: 'Conversions'
    }
  ];

  return (
    <Box
      sx={{
        backgroundColor: 'background.default',
        minHeight: '100%',
        p: 3
      }}
    >
      <Container maxWidth="md">
        <Card {...props} sx={{ p: 2 }}>
          <Box
            sx={{
              alignItems: 'center',
              display: 'flex',
              justifyContent: 'space-between',
              flexWrap: 'wrap'
            }}
          >
            <Box
              sx={{
                alignItems: 'center',
                display: 'flex'
              }}
            >
              <Avatar
                sx={{
                  backgroundColor: 'primary.main',
                  color: 'primary.contrastText'
                }}
              >
                <RefreshIcon fontSize="small" />
              </Avatar>
              <Box sx={{ ml: 3 }}>
                <Typography
                  color="textSecondary"
                  noWrap
                  variant="body1"
                >
                  Conversions (7 days)
                </Typography>
                <Typography variant="h4">
                  361
                </Typography>
              </Box>
            </Box>
            <Box sx={{ maxWidth: 200 }}>
              <Chart
                height={100}
                type="line"
                options={chartOptions}
                series={chartSeries}
              />
            </Box>
          </Box>
        </Card>
        
      </Container>
    </Box>
  );
};

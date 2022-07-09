/*
import { format } from 'date-fns';
import { Box, Card, CardHeader, Divider } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { Chart } from '../../chart';
import { Scrollbar } from '../../scrollbar';

const data = {
  series: [
    { data: [12, 24, 36, 48, 60, 72, 84] },
    { data: [12, 24, 36, 48, 60, 72, 84] },
    { data: [12, 24, 36, 48, 60, 72, 84] }
  ],
  categories: [
    'Capital One',
    'Ally Bank',
    'ING',
    'Ridgewood',
    'BT Transilvania',
    'CEC',
    'CBC'
  ]
};

export const OverviewTotalTransactions = (props) => {
  const theme = useTheme();

  const chartOptions = {
    chart: {
      background: 'transparent',
      stacked: true,
      toolbar: {
        show: false
      }
    },
    colors: ['#6E7AD8', '#4655CE', '#2F3EB1'],
    dataLabels: {
      enabled: false
    },
    fill: {
      opacity: 1
    },
    grid: {
      borderColor: theme.palette.divider,
      xaxis: {
        lines: {
          show: true
        }
      },
      yaxis: {
        lines: {
          show: true
        }
      }
    },
    states: {
      active: {
        filter: {
          type: 'none'
        }
      },
      hover: {
        filter: {
          type: 'none'
        }
      }
    },
    legend: {
      show: false
    },
    stroke: {
      colors: ['transparent'],
      show: true,
      width: 2
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
      categories: data.categories,
      labels: {
        style: {
          colors: '#A3A3A3'
        }
      }
    },
    yaxis: {
      labels: {
        offsetX: -12,
        style: {
          colors: '#A3A3A3'
        }
      }
    }
  };

  const chartSeries = data.series;

  return (
    <Card {...props}>
      <CardHeader
        subheader={format(new Date(), 'MMM yyyy')}
        title="Total Transactions"
      />
      <Divider />
      <Scrollbar>
        <Box
          sx={{
            height: 336,
            minWidth: 500,
            px: 2
          }}
        >
          <Chart
            height={300}
            options={chartOptions}
            series={chartSeries}
            type="bar"
          />
        </Box>
      </Scrollbar>
    </Card>
  );
};
*/
import { Box, Card, CardContent, CardHeader } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { Chart } from '../../chart';

export const OverviewTotalTransactions = (props) => {
  const theme = useTheme();

  const chartOptions = {
    chart: {
      background: 'transparent',
      toolbar: {
        show: false
      }
    },
    colors: ['#13affe', '#fbab49'],
    dataLabels: {
      enabled: false
    },
    fill: {
      opacity: 1
    },
    grid: {
      borderColor: theme.palette.divider,
      yaxis: {
        lines: {
          show: false
        }
      }
    },
    legend: {
      labels: {
        colors: theme.palette.text.secondary
      },
      show: true
    },
    plotOptions: {
      bar: {
        columnWidth: '40%'
      }
    },
    stroke: {
      colors: ['transparent'],
      show: true,
      width: 2
    },
    theme: {
      mode: theme.palette.mode
    },
    xaxis: {
      axisBorder: {
        show: true,
        color: theme.palette.divider
      },
      axisTicks: {
        show: true,
        color: theme.palette.divider
      },
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
      labels: {
        style: {
          colors: theme.palette.text.secondary
        }
      }
    },
    yaxis: {
      axisBorder: {
        color: theme.palette.divider,
        show: true
      },
      axisTicks: {
        color: theme.palette.divider,
        show: true
      },
      labels: {
        style: {
          colors: theme.palette.text.secondary
        }
      }
    }
  };

  const chartSeries = [
    {
      data: [30, 40, 25, 50, 49, 21, 70, 51],
      name: 'This week'
    },
    {
      data: [23, 12, 54, 61, 32, 56, 81, 19],
      name: 'Last week'
    }
  ];

  return (
    <Box
      sx={{
        backgroundColor: 'background.default',
        p: 3
      }}
    >
      <Card {...props}>
        <CardHeader title="Expenditure by Department" />
        <CardContent>
          <Chart
            height={300}
            options={chartOptions}
            series={chartSeries}
            type="bar"
          />
        </CardContent>
      </Card>
    </Box>
  );
};

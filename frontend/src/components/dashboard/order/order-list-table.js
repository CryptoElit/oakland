import PropTypes from 'prop-types';
import { format } from 'date-fns';
import numeral from 'numeral';
import {
  Box,
  Checkbox,
  Table,
  TableBody,
  TableCell,
  TablePagination,
  TableRow,
  Typography
} from '@mui/material';
import { SeverityPill } from '../../severity-pill';

const severityMap = {
  complete: 'success',
  pending: 'info',
  canceled: 'warning',
  rejected: 'error'
};

export const OrderListTable = (props) => {
  const {
    onOpenDrawer,
    onPageChange,
    onRowsPerPageChange,
    orders,
    ordersCount,
    page,
    rowsPerPage,
    ...other
  } = props;

  return (
    <div {...other}>
      <Table>
        <TableBody>
          {orders.map((order) => (
            <TableRow
              hover
              key={order.id}
              onClick={() => onOpenDrawer?.(order.id)}
              sx={{ cursor: 'pointer' }}
            >
              <TableCell padding="checkbox">
                <Checkbox />
              </TableCell>
              <TableCell>
                
              </TableCell>
              <TableCell align="right"
                sx={{
                  alignItems: 'center',
                  display: 'flex'
                }}
              >
                <Box
                  sx={{
                    backgroundColor: (theme) => theme.palette.mode === 'dark'
                      ? 'neutral.800'
                      : 'neutral.200',
                    borderRadius: 2,
                    maxWidth: 'fit-content',
                    ml: 3,
                    p: 1
                  }}
                >
                  <Typography
                    color="textSecondary"
                    variant="body2"
                  >
                    {format(order.createdAt, 'dd MMM yyyy | HH:mm')}
                  </Typography>
                  
                </Box>
                <Box sx={{ ml: 2 }}>
                  <Typography variant="subtitle2">
                    {order.number}
                  </Typography>
                  <Typography
                    color="textSecondary"
                    variant="body2"
                  >
                    Total of
                    {' '}
                    R {numeral(order.totalAmount).format(0,0.00)}
                  </Typography>
                </Box>
              </TableCell>
              <TableCell align="right">
                <SeverityPill color={severityMap[order.status] || 'warning'}>
                  {order.status}
                </SeverityPill>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <TablePagination
        component="div"
        count={ordersCount}
        onPageChange={onPageChange}
        onRowsPerPageChange={onRowsPerPageChange}
        page={page}
        rowsPerPage={rowsPerPage}
        rowsPerPageOptions={[5, 10, 25]}
      />
    </div>
  );
};

OrderListTable.propTypes = {
  onOpenDrawer: PropTypes.func,
  onPageChange: PropTypes.func.isRequired,
  onRowsPerPageChange: PropTypes.func,
  orders: PropTypes.array.isRequired,
  ordersCount: PropTypes.number.isRequired,
  page: PropTypes.number.isRequired,
  rowsPerPage: PropTypes.number.isRequired
};

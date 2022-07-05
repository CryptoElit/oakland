import {useState} from 'react';
import Box from "@mui/material/Box";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/DeleteOutlined";
import {
  DataGrid,
  GRID_CHECKBOX_SELECTION_FIELD,
  GridToolbarContainer,
  GridToolbarExport,
  GridToolbarQuickFilter
} from "@mui/x-data-grid";
import { styled } from "@mui/material/styles";

export const DataGridHelper = () => {
  const [pagination, setPagination] = useState({
    pagination: true,
    autoPageSize: false,
    pageSize: 1
  });


  const DataGridCustom = styled(DataGrid)(({ theme }) => ({
    border: `1px solid ${theme.palette.mode === "light" ? "#f0f0f0" : "#303030"}`,
    color:
      theme.palette.mode === "light"
        ? "rgba(0,0,0,.85)"
        : "rgba(255,255,255,0.85)",
    fontFamily: [
      "-apple-system",
      "BlinkMacSystemFont",
      '"Segoe UI"',
      "Roboto",
      '"Helvetica Neue"',
      "Arial",
      "sans-serif",
      '"Apple Color Emoji"',
      '"Segoe UI Emoji"',
      '"Segoe UI Symbol"'
    ].join(","),
    WebkitFontSmoothing: "auto",
    letterSpacing: "normal",
    "& .MuiDataGrid-columnsContainer": {
      backgroundColor: theme.palette.mode === "light" ? "#fafafa" : "#1d1d1d"
    },
    "& .MuiDataGrid-iconSeparator": {
      display: "none"
    },
    "& .MuiDataGrid-columnHeader, .MuiDataGrid-cell": {
      borderRight: `1px solid ${
        theme.palette.mode === "light" ? "#f0f0f0" : "#303030"
      }`
    },
    "& .MuiDataGrid-columnsContainer, .MuiDataGrid-cell": {
      borderBottom: `1px solid ${
        theme.palette.mode === "light" ? "#f0f0f0" : "#303030"
      }`
    },
    "& .MuiDataGrid-cell": {
      color:
        theme.palette.mode === "light"
          ? "rgba(0,0,0,.85)"
          : "rgba(255,255,255,0.85)",
      fontFamily: [
        "-apple-system",
        "BlinkMacSystemFont",
        '"Segoe UI"',
        "Roboto",
        '"Helvetica Neue"',
        "Arial",
        "sans-serif",
        '"Apple Color Emoji"',
        '"Segoe UI Emoji"',
        '"Segoe UI Symbol"'
      ].join(","),
      WebkitFontSmoothing: "auto",
      letterSpacing: "normal",
      "& .MuiDataGrid-columnsContainer": {
        backgroundColor: theme.palette.mode === "light" ? "#fafafa" : "#1d1d1d"
      },
      "& .MuiDataGrid-iconSeparator": {
        display: "none"
      },
      "& .MuiDataGrid-colCell, .MuiDataGrid-cell": {
        borderRight: `1px solid ${
          theme.palette.mode === "light" ? "#f0f0f0" : "#303030"
        }`
      },
      "& .MuiDataGrid-columnsContainer, .MuiDataGrid-cell": {
        borderBottom: `1px solid ${
          theme.palette.mode === "light" ? "#f0f0f0" : "#303030"
        }`
      },
      "& .MuiDataGrid-cell": {
        color:
          theme.palette.mode === "light"
            ? "rgba(0,0,0,.85)"
            : "rgba(255,255,255,0.65)"
      },
      "& .MuiPaginationItem-root": {
        borderRadius: 0
      },
      "& .MuiCheckbox-root svg": {
        width: 16,
        height: 16,
        backgroundColor: "transparent",
        border: `1px solid ${
          theme.palette.mode === "light" ? "#d9d9d9" : "rgb(67, 67, 67)"
        }`,
        borderRadius: 2
      },
      "& .MuiCheckbox-root svg path": {
        display: "none"
      },
      "& .MuiCheckbox-root.Mui-checked:not(.MuiCheckbox-indeterminate) svg": {
        backgroundColor: "#1890ff",
        borderColor: "#1890ff"
      },
      "& .MuiCheckbox-root.Mui-checked .MuiIconButton-label:after": {
        position: "absolute",
        display: "table",
        border: "2px solid #fff",
        borderTop: 0,
        borderLeft: 0,
        transform: "rotate(45deg) translate(-50%,-50%)",
        opacity: 1,
        transition: "all .2s cubic-bezier(.12,.4,.29,1.46) .1s",
        content: '""',
        top: "50%",
        left: "39%",
        width: 5.71428571,
        height: 9.14285714
      },
      "& .MuiCheckbox-root.MuiCheckbox-indeterminate .MuiIconButton-label:after": {
        width: 8,
        height: 8,
        backgroundColor: "#1890ff",
        transform: "none",
        top: "39%",
        border: 0
      }
    }
  }));
  
  const StyledBox = styled(Box)(({ theme }) => ({
    display: "flex",
    flexDirection: "column",
    height: 600,
    width: "100%",
    "& .MuiFormGroup-options": {
      alignItems: "center",
      paddingBottom: theme.spacing(1),
      "& > div": {
        minWidth: 100,
        margin: theme.spacing(2),
        marginLeft: 0
      }
    }
  }));
  

  const CustomToolbar = () => {
    return (
      <GridToolbarContainer>
        <GridToolbarQuickFilter />
        <GridToolbarExport />
      </GridToolbarContainer>
    );
  };

  return (
   
     <StyledBox>
      <DataGridCustom
        rows={[
          { id: 1, parish: "Snow", status: false, age: 35 },
          { id: 2, lastName: "Sdow", firstName: "fon", age: 35 }
        ]}
        columns={[
          { field: "id", headerName: "ID", width: 90 },
          {
            field: "case_number",
            headerName: "Case Number",
            flex: 1
          },
          {
            field: "parish",
            headerName: "Parish",
            flex: 1
          },
          {
            field: "status",
            headerName: "Status",
            type: "boolean",
            flex: 1
          },
          {
            field: "amount",
            headerName: "Amount",
            type: "number",
            flex: 1
          },
          {
            field: "date_filed",
            headerName: "Date Filed",

            flex: 1
          },
          {
            field: "defendant_name",
            headerName: "Defendant Name",

            flex: 1
          },
          {
            field: "state",
            headerName: "State",
            flex: 1
          },
          {
            field: "city",
            headerName: "City",

            flex: 1
          },
          {
            field: "zip_code",
            headerName: "Zip Code",
            type: "number",
            flex: 1
          },
          {
            field: "action",
            headerName: "Action",
            sortable: false,
            renderCell: (params) => {
              return (
                <>
                  <EditIcon onClick={() => alert("clicked")} />
                  <DeleteIcon onClick={() => alert("clicked")} />
                </>
              );
            }
          }
        ]}
        components={{
          Toolbar: CustomToolbar
        }}
        componentsProps={{
          toolbar: { showQuickFilter: true }
        }}
        checkboxSelection
        disableSelectionOnClick
        rowThreshold={0}
        initialState={{
          pinnedColumns: {
            left: [GRID_CHECKBOX_SELECTION_FIELD, "order_number"]
          }
        }}
        {...pagination}
      />
    </StyledBox>
  
  );
};

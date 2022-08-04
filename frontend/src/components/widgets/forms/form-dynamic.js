import React, { useState, Fragment } from "react";
import { Autocomplete, Avatar, Box, Button, Chip, Divider, FormControlLabel, Grid, IconButton, TextField, Typography, Select, Switch } from '@mui/material';
import { Plus as PlusIcon } from '../../../icons/plus';
import { Trash as TrashIcon } from '../../../icons/trash';
import { de } from "date-fns/locale";

const suppliers = [
    { text: 'Midas (Pty) Ltd', value: 'JE' },
    { text: 'Electrical Supplier', value: 'JO' },
    { text: 'Kazakhstan', value: 'KZ' },
    { text: 'Kenya', value: 'KE' },
    { text: 'Kiribati', value: 'KI' },
    { text: 'Korea, Democratic People\'S Republic of', value: 'KP' },
    { text: 'Korea, Republic of', value: 'KR' },
    { text: 'Kuwait', value: 'KW' },
    { text: 'Kyrgyzstan', value: 'KG' },
    { text: 'Lao People\'S Democratic Republic', value: 'LA' }
  ];

  const items = [
    { text : 'Screwdriver', value: 'SC'},
    { text: 'Battery', value: 'BT' },
    { text: 'Wrench', value: 'WR' },
    { text: 'Power Cables', value: 'PC' },
    { text: 'Hammer', value: 'HR' },
    { text: 'Nails', value: 'NS' },
    { text: 'Grinder', value: 'GR' },
    { text: 'Gloves', value: 'GS' },
    { text: 'Sanders', value: 'SD' },
    { text: 'Blowers', value: 'BL' }
  ];


export const DynamicForm = () => {
  const [inputFields, setInputFields] = useState([
    { desc: '', quantity: '1', price:'0.00'}
  ]);

  const handleAddFields = () => {
    const values = [...inputFields, { desc: '', quantity: '1', price:'0.00'}];
    setInputFields(values);
  };

  const handleRemoveFields = index => {
    const values = [...inputFields];
    values.splice(index, 1);
    setInputFields(values);
  };

  const handleInputChange = (index, event) => {
    const values = [...inputFields];
    console.log("==> old values", values)
    console.log("==> old values[index]",values[index])
    console.log("==> old event.target",event.target)
    console.log("==> old event.target.name",event.target.name)
    console.log("==> old event.target.value",event.target.value)
    values[index][event.target.name] = event.target.value;

    setInputFields(values);
  };

  const handleSubmit = e => {
    e.preventDefault();
    // handle form submission here
    alert(JSON.strngify(iinputFields, null, 2))
  };

  const resetForm = e => setInputFields([{ desc:'', quantity: '', price:'', supply:'' }])

  

  return (
    <>
        <Box>
        
      <form onSubmit={handleSubmit}>
        <div className="form-row">
        <Box sx={{ my:1 }}>
        <Grid sx={{ mb: 3}}
          container
            spacing={5}
          >
   
          <Grid item
          md={9}
          xs={12}
          >
            <Typography
         
          variant="h5"
        >
          Order Details
        </Typography>
          </Grid>
          
          <Grid
              item
              md={3}
              xs={12}
            >
              <Box sx={{ 
                border: (theme) => `1px solid ${theme.palette.divider}`,
            borderRadius: 20,
                mt: 0
                }}>
                <FormControlLabel
                  control={<Switch />}
                  label="Petty Cash Order"
                />
              </Box>
            </Grid>
          </Grid>
        </Box>
        

        
             <Grid
            container
            spacing={3}
          >

            <Grid
            item
            md={9}
            xs={12}
          >
            <Autocomplete
              getOptionLabel={(option) => option.text}
              options={suppliers}
              renderInput={(params) => (
                <TextField {...params}
                           fullWidth
                           label="Select Supplier"
                           name="supply"
                />
              )}
            />
          </Grid>
          </Grid>
          <Grid sx={{my: 0}}
            container
            spacing={3}
          >
          <Grid
            item
            md={5}
            xs={12}
          >
            <TextField 
              fullWidth
              label="Doc Nr"
              name="doc-nr"
            />
          </Grid>
        
           
        
          </Grid>
        
          
          <Box sx={{ my: 4 }}>
          <Grid
          container
            spacing={3}
          >

          <Grid item
          md={3}
          xs={12}
          >
            <Typography
          sx={{ mt: 1 }}
          variant="h5"
        >
          Item Details
        </Typography>
          </Grid>
          </Grid>
          </Box>
          {inputFields.map((inputField, index) => (
            <Fragment key={`${inputField}~${index}`}>
              <div className="form-group col-sm-6">
              <Box
            sx={{
              mt: 3
            }}
          >
              <Grid
                container
                spacing={3}
              >
          <Grid
            
            item
            sm={1}
            xs={12}
          >
            <TextField
              fullWidth
              label="Qty"
              name="quantity"
              type="number"
              value={inputField.quantity}
                onChange={event => handleInputChange(index, event)}
            />
          </Grid>   
          <Grid
            item
            sm={8}
            xs={12}
          >
         <Autocomplete
        multiple
        id="tags-filled"
        options={items.map((option) => option.text)}
        freeSolo
        variant="filled"
        placeholder="Description"
        renderTags={(value, getTagProps) =>
          value.map((option, index) => (
            <Chip variant="outlined" key={index} label={option} {...getTagProps({ index })} />
          ))
        }
        renderInput={(params) => (
          <TextField
            {...params}
            fullWidth
              name="desc"
              label="Description"
              value={inputField.desc}
                onChange={event => handleInputChange(index, event)}
                />
              )}
            />
          </Grid>
          
          <Grid
            
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Price"
              name="price"
              type="number"
              value={inputField.price}
                onChange={event => handleInputChange(index, event)}
            />
          </Grid>
          <Grid
            
            item
            sm={1}
            xs={12}
          >
           
           <Button
                  className="btn btn-link"
                  type="button"
                  onClick={() => handleRemoveFields(index)}
                >
                  <TrashIcon sx={{ mt : "5px", mr : "15px", color : 'red'}}
          
                  />
                </Button>

          </Grid>
          </Grid>
          </Box>
            

                </div>
              
      
            </Fragment>
          ))}
          <div style={{textAlign: "right", marginTop: '25px'}}>
          <Button
                  className="btn btn-link"
                  type="button"
                  variant="contained"
                  onClick={() => handleAddFields()}
                >
              <PlusIcon />Add Item
                </Button>
        </div>
        </div>
      </form>
      </Box>
    </>
  );
};



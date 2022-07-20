import React, { useState, Fragment } from "react";
import { Autocomplete, Avatar, Box, Button, Chip, FormControlLabel, Grid, IconButton, TextField, Typography, Switch } from '@mui/material';
import { Plus as PlusIcon } from '../../../icons/plus';
import { de } from "date-fns/locale";

const suppliers = [
    { text: 'Midas (Pty) Ltd', value: 'JE' },
    { text: 'Jordan', value: 'JO' },
    { text: 'Kazakhstan', value: 'KZ' },
    { text: 'Kenya', value: 'KE' },
    { text: 'Kiribati', value: 'KI' },
    { text: 'Korea, Democratic People\'S Republic of', value: 'KP' },
    { text: 'Korea, Republic of', value: 'KR' },
    { text: 'Kuwait', value: 'KW' },
    { text: 'Kyrgyzstan', value: 'KG' },
    { text: 'Lao People\'S Democratic Republic', value: 'LA' }
  ];

export const DynamicForm = () => {
  const [inputFields, setInputFields] = useState([
    { desc: '', quantity: '', price:'0', supply: ''}
  ]);

  const handleAddFields = () => {
    const values = [...inputFields];
    values.push({ desc: '', quantity: '', price:'', supply: ''});
    setInputFields(values);
  };

  const handleRemoveFields = index => {
    const values = [...inputFields];
    values.splice(index, 1);
    setInputFields(values);
  };

  const handleInputChange = (index, event) => {
    const values = [...inputFields];
    if (event.target.name === "desc") {
      values[index].desc = event.target.value;
    } else if (event.target.name === "quantity") {
      values[index].quantity = event.target.value;
    } else
    {
      values[index].price = event.target.value;
    }

    setInputFields(values);
  };

  const handleSubmit = e => {
    e.preventDefault();
    // handle form submission here
    alert(JSON.stringify(inputFields, null, 2))
  };

  const resetForm = e => setInputFields([{ desc:'', quantity: '', price:'', supply:'' }])

  return (
    <>
        <Box>
        
      <form onSubmit={handleSubmit}>
        <div className="form-row">
        <Box sx={{ my:3 }}>
        <Grid
          container
            spacing={1}
          >
   
          <Grid item
          md={8}
          xs={12}
          >
            <Typography
          sx={{ mt: 1 }}
          variant="h6"
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
            md={6}
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
          <Box sx={{ my: 4 }}>
          <Grid
          container
            spacing={3}
          >

          <Grid item
          md={2}
          xs={12}
          >
            <Typography
          sx={{ mt: 1 }}
          variant="h6"
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
            sm={8}
            xs={12}
          >
            <TextField
            fullWidth
              label="Description"
              name="desc"
              value={inputField.desc}
                onChange={event => handleInputChange(index, event)}
            />
          </Grid>
          <Grid
            
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Quantity"
              name="quantity"
              type="number"
              value={inputField.quantity}
                onChange={event => handleInputChange(index, event)}
            />
          </Grid>
          <Grid
            
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Estimated Price"
              name="price"
              type="number"
              value={inputField.price}
                onChange={event => handleInputChange(index, event)}
            />
          </Grid>
          </Grid>
          </Box>
            

                </div>
                
      

              <div className="form-group col-sm-2">
                <button
                  className="btn btn-link"
                  type="button"
                  disabled={index === 0}
                  onClick={() => handleRemoveFields(index)}
                >
                  -
                </button>
                <button
                  className="btn btn-link"
                  type="button"
                  onClick={() => handleAddFields()}
                >
                  +
                </button>
              </div>
            </Fragment>
          ))}
        </div>
        <div className="submit-button">
          <button
            className="btn btn-primary mr-2"
            type="submit"
            onSubmit={handleSubmit}
          >
            Save
          </button>
          <button
            className="btn btn-secondary mr-2"
            type="reset"
            onClick={resetForm}
          >
            Reset Form
          </button>
        </div>
        <br/>
        <pre>
          {JSON.stringify(inputFields, null, 2)}
        </pre>
      </form>
      </Box>
    </>
  );
};



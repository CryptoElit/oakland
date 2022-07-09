import React, { useState, Fragment } from "react";
import { Autocomplete, Avatar, Box, Button, Chip, Grid, IconButton, TextField, Typography } from '@mui/material';
import { Plus as PlusIcon } from '../../../icons/plus';

const countries = [
    { text: 'Jersey', value: 'JE' },
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
    { firstName: '', lastName: '' }
  ]);

  const handleAddFields = () => {
    const values = [...inputFields];
    values.push({ firstName: '', lastName: '' });
    setInputFields(values);
  };

  const handleRemoveFields = index => {
    const values = [...inputFields];
    values.splice(index, 1);
    setInputFields(values);
  };

  const handleInputChange = (index, event) => {
    const values = [...inputFields];
    if (event.target.name === "firstName") {
      values[index].firstName = event.target.value;
    } else {
      values[index].lastName = event.target.value;
    }

    setInputFields(values);
  };

  const handleSubmit = e => {
    e.preventDefault();
    // handle form submission here
    alert(JSON.stringify(inputFields, null, 2))
  };

  const resetForm = e => setInputFields([{ firstName: '', lastName: '' }])

  return (
    <>
        <Box>
      <h1>Dynamic Form Fields in React</h1>
      <form onSubmit={handleSubmit}>
        <div className="form-row">
          {inputFields.map((inputField, index) => (
            <Fragment key={`${inputField}~${index}`}>
              <div className="form-group col-sm-6">
              <Box
            sx={{
              alignItems: 'center',
              display: 'flex',
              mt: 3
            }}
          >
            <TextField
              fullWidth
              label="First Name"
              name="firstName"
              value={inputField.firstName}
                onChange={event => handleInputChange(index, event)}
            />
            <IconButton sx={{ ml: 2 }}>
              <PlusIcon fontSize="small" 
                
              />

            </IconButton>
          </Box>

                </div>
                
              <div className="form-group col-sm-4">
                <Box
            sx={{
              alignItems: 'center',
              display: 'flex',
              mt: 3
            }}
          >
            <TextField
              fullWidth
              label="Last Name"
              name="lastName"
              value={inputField.lastName}
                onChange={event => handleInputChange(index, event)}
            />
            <IconButton sx={{ ml: 2 }}>
              <PlusIcon fontSize="small" />
            </IconButton>
          </Box>

              </div>
              <Box sx={{ mt: 3 }}>
        <Grid
          container
          spacing={3}
        >
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Case Type"
              name="firstName"
            />
          </Grid>
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Case Year"
              name="lastName"
            />
          </Grid>
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Case Number"
              name="address"
            />
          </Grid>
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Citation/File Number"
              name="optionalAddress"
            />
          </Grid>
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="State"
              name="state"
            />
          </Grid>
          <Grid
            item
            sm={2}
            xs={12}
          >
            <TextField
              fullWidth
              label="Zip"
              name="zip"
            />
          </Grid>
        </Grid>
      </Box>

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



import PptxGenJS from 'pptxgenjs';

// Function to convert district number to ordinal (1st, 2nd, 3rd, etc.)
const getOrdinal = (number) => {
  if (number === 1) return '1st';
  if (number === 2) return '2nd';
  if (number === 3) return '3rd';
  return `${number}th`; // For any other number (4th, 5th, etc.)
};

// Function to format and generate PowerPoint report
export const generateProgramPpt = (provinces, programs) => {
  const pptx = new PptxGenJS();

  // Iterate over each province
  provinces.forEach(province => {
    // Slide 1: Province Overview
    const firstSlide = pptx.addSlide();
    firstSlide.background = { path: `${window.location.origin}/ppd-images/ppt-bg.png` };

    // Add province name as the main title
    firstSlide.addText(province.col_province.toUpperCase(), { 
      x: '-10%', y: '42%', w: '100%', fontSize: 44, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
    });

    // Add "TARGET AND ACCOMPLISHMENT" below the province name
    firstSlide.addText('TARGET AND ACCOMPLISHMENT', { 
      x: '-10%', y: '52%', w: '100%', fontSize: 28, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
    });

    // Add current date for the slide in long format
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const currentDate = new Date().toLocaleDateString(undefined, options);
    firstSlide.addText(`As of ${currentDate}`, { 
      x: '-10%', y: '62%', w: '100%', fontSize: 22, bold: true, color: '0000FF', fontFace: 'Arial', align: 'center' 
    });
 // Slide 2: Total Utilization Summary for the Province
 const provinceUtilizationSlide = pptx.addSlide();
 provinceUtilizationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-total.png` };

 // Calculate total fund utilized for all cities in the province
 let totalFundUtilizedPerProvince = 0;

 Object.values(province.districts || {}).forEach(district => {
   district.cities.forEach(city => {
     const utilized = city.total_utilization || 0; // Sum the total utilization for each city
     totalFundUtilizedPerProvince += utilized; // Add to the total fund utilized for the province
   });
 });

 // Add "TOTAL FUND UTILIZED" for the province
 provinceUtilizationSlide.addText(`TOTAL FUND UTILIZED FOR ${province.col_province.toUpperCase()}`, { 
   x: '-5%', y: '42%', w: '90%', fontSize: 23, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
 });

 // Add the calculated total fund utilized for the province
 provinceUtilizationSlide.addText(`₱${totalFundUtilizedPerProvince.toLocaleString()}`, { 
   x: '-10%', y: '52%', w: '90%', fontSize: 50, bold: true, color: '0000FF', fontFace: 'Arial', align: 'center'
 });


     // Initialize arrays to store total allocations, utilizations, targets, and served amounts per program
     const programAllocations = {};
     const programUtilizations = {};
     const programTargets = {};
     const programServed = {};
 
     programs.forEach(program => {
       // Slide 2: Allocation Summary for the Program
       const allocationSlide = pptx.addSlide();
       allocationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
       // Add Program Name Header
       allocationSlide.addText(` ${program.program_name}`, {
         x: 0.9,
         y: 0.5, w: '60%', fontSize: 20, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
 // Add the program logo
 allocationSlide.addImage({
    path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
    x: 0.5, y: 0.5, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
  });
       allocationSlide.addText(`${province.col_province.toUpperCase()}`, {
         x: 0.9,
         y: 1.0, w: '60%', fontSize: 16, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
 
       const allocationTableHeader = [
         [
           { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
           { text: 'TARGET', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
         ],
         [
           { text: 'Municipality', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Physical', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Fund Allocated (Php)', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
         ]
       ];
 
       const allocationRows = [];
       let totalPhysicalTarget = 0;
       let totalFundAllocated = 0;
 
       Object.values(province.districts || {}).forEach(district => {
         if (district.district) {
           allocationRows.push([
             { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 8, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
           ]);
         }
 
         district.cities.forEach((city, index) => {
           const fillColor = index % 2 === 0 ? 'F0F8FF' : 'E6E6FA';
           const target = city.total_target || 0;
           const allocated = city.total_allocation || 0;
 
           totalPhysicalTarget += target;
           totalFundAllocated += allocated;
 
           // Store total allocations and targets per program
           programAllocations[program.program_name] = (programAllocations[program.program_name] || 0) + allocated;
           programTargets[program.program_name] = (programTargets[program.program_name] || 0) + target;
 
           allocationRows.push([
             { text: city.col_citymuni, options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: fillColor } },
             { text: target.toString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } },
             { text: allocated.toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } }
           ]);
         });
       });
 
       allocationRows.push([
         { text: 'TOTAL', options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
         { text: totalPhysicalTarget.toString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
         { text: totalFundAllocated.toLocaleString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } }
       ]);
 
       allocationSlide.addTable([...allocationTableHeader, ...allocationRows], {
         x: 0.5, y: 1.5, w: 8, fontSize: 10, border: { pt: 1, color: '000000' }
       });
 
       // Slide 3: Utilization Summary for the Program
       const utilizationSlide = pptx.addSlide();
       utilizationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
 
       // Add Program Name Header
       utilizationSlide.addText(` ${program.program_name}`, {
         x: 0.9,
         y: 0.5, w: '60%', fontSize: 20, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
       utilizationSlide.addImage({
        path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
        x: 0.5, y: 0.5, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
      });
 
       utilizationSlide.addText(`${province.col_province.toUpperCase()}`, {
         x: 0.9,
         y: 1.0, w: '60%', fontSize: 16, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
       });
 
       const utilizationTableHeader = [
         [
           { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
           { text: 'UTILIZED', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
           { text: 'SERVED', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } }
         ],
         [
           { text: 'Municipality', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
           { text: 'Total Served', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
         ]
       ];
 
       const utilizationRows = [];
       let totalFundUtilized = 0;
       let totalServed = 0;
 
       Object.values(province.districts || {}).forEach(district => {
         if (district.district) {
           utilizationRows.push([
             { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 8, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
           ]);
         }
 
         district.cities.forEach((city, index) => {
           const fillColor = index % 2 === 0 ? 'F0F8FF' : 'E6E6FA';
           const utilized = city.total_utilized || 0;
           const served = city.total_served || 0;
 
           totalFundUtilized += utilized;
           totalServed += served;
 
           // Store total utilizations and served amounts per program
           programUtilizations[program.program_name] = (programUtilizations[program.program_name] || 0) + utilized;
           programServed[program.program_name] = (programServed[program.program_name] || 0) + served;
 
           utilizationRows.push([
             { text: city.col_citymuni, options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: fillColor } },
             { text: utilized.toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } },
             { text: served.toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } }
           ]);
         });
       });
 
       utilizationRows.push([
         { text: 'TOTAL', options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
         { text: totalFundUtilized.toLocaleString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
         { text: totalServed.toLocaleString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } }
       ]);
 
       utilizationSlide.addTable([...utilizationTableHeader, ...utilizationRows], {
         x: 0.5, y: 1.5, w: 8, fontSize: 10, border: { pt: 1, color: '000000' }
       });
     });
 
// Overview Slide for Allocations
const allocationOverviewSlide = pptx.addSlide();
allocationOverviewSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

// Overview Title
allocationOverviewSlide.addText('ALLOCATIONS OVERVIEW', { x: 0.5, y: 0.5, fontSize: 24, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' });

// Allocations Table Header
const allocationOverviewHeader = [
  [
    { text: 'LOGO', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'PROGRAM', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'TARGET', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'ALLOCATED', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } }
  ]
];

// Allocations Overview Rows with Program Logo
const allocationOverviewRows = Object.keys(programAllocations).map(programName => {
  const program = programs.find(p => p.program_name === programName);
  
  return [
    {
      text: '', // Placeholder for logo image (will be filled separately)
      options: { fontSize: 8, align: 'center', fontFace: 'Arial', fill: 'FFFFFF' }
    },
    {
      text: programName, // Program name text
      options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: 'FFFFFF' }
    },
    { text: programTargets[programName].toString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } },
    { text: programAllocations[programName].toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } }
  ];
});

// Add table to slide
const allocationTable = allocationOverviewSlide.addTable([...allocationOverviewHeader, ...allocationOverviewRows], {
  x: 0.5, y: 1.5, w: 9, fontSize: 10, border: { pt: 1, color: '000000' }
});

// Set up images in the logo column
allocationOverviewRows.forEach((row, index) => {
  const program = programs.find(p => p.program_name === row[1].text); // Get the correct program
  if (program && program.program_logo) {
    const logoPath = `${window.location.origin}/${program.program_logo}`;
    console.log(`Loading image from: ${logoPath}`); // Debugging output

    // Place the image in the logo column
    allocationOverviewSlide.addImage({
      path: logoPath,
      x: 0.5, // Adjust x position to the left of the table
      y: 1.5 + index * 0.4, // Ensure enough spacing for each row
      w: 0.5, // Width of the logo
      h: 0.5  // Height of the logo
    });
  } else {
    console.warn(`Logo not found for program: ${program ? program.program_name : 'unknown'}`); // Fallback logging
  }
});

// Similar adjustments for Utilization Overview
const utilizationOverviewSlide = pptx.addSlide();
utilizationOverviewSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

// Overview Title
utilizationOverviewSlide.addText('UTILIZATIONS OVERVIEW', { x: 0.5, y: 0.5, fontSize: 24, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' });

// Utilization Table Header
const utilizationOverviewHeader = [
  [
    { text: 'LOGO', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'PROGRAM', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'UTILIZED', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
    { text: 'SERVED', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } }
  ]
];

// Utilization Overview Rows with Program Logo
const utilizationOverviewRows = Object.keys(programUtilizations).map(programName => {
  const program = programs.find(p => p.program_name === programName);
  
  return [
    {
      text: '', // Placeholder for logo image (will be filled separately)
      options: { fontSize: 8, align: 'center', fontFace: 'Arial', fill: 'FFFFFF' }
    },
    {
      text: programName, // Program name text
      options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: 'FFFFFF' }
    },
    { text: programUtilizations[programName].toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } },
    { text: programServed[programName].toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } }
  ];
});

// Add table to slide
const utilizationTable = utilizationOverviewSlide.addTable([...utilizationOverviewHeader, ...utilizationOverviewRows], {
  x: 0.5, y: 1.5, w: 9, fontSize: 10, border: { pt: 1, color: '000000' }
});

// Set up images in the logo column
utilizationOverviewRows.forEach((row, index) => {
  const program = programs.find(p => p.program_name === row[1].text); // Get the correct program
  if (program && program.program_logo) {
    const logoPath = `${window.location.origin}/${program.program_logo}`;
    console.log(`Loading image from: ${logoPath}`); // Debugging output

    // Place the image in the logo column
    utilizationOverviewSlide.addImage({
      path: logoPath,
      x: 0.5, // Adjust x position to the left of the table
      y: 1.5 + index * 0.4, // Ensure enough spacing for each row
      w: 0.5, // Width of the logo
      h: 0.5  // Height of the logo
    });
  } else {
    console.warn(`Logo not found for program: ${program ? program.program_name : 'unknown'}`); // Fallback logging
  }
});


});
 
   // Save the PowerPoint file
   pptx.writeFile({ fileName: 'Program_Overview_Report.pptx' });
 };
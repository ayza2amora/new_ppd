import PptxGenJS from 'pptxgenjs';

// Function to convert district number to ordinal (1st, 2nd, 3rd, etc.)
const getOrdinal = (number) => {
  if (number === 1) return '1st';
  if (number === 2) return '2nd';
  if (number === 3) return '3rd';
  return `${number}th`; // For any other number (4th, 5th, etc.)
};

// Function to format and generate PowerPoint report
export const generatePptReport = (provinces, programs) => {
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

   // Create slides for city-level data grouped by districts
   Object.values(province.districts || {}).forEach((district) => {
    district.cities.forEach((city) => {
      // Initialize totals for the city
      let totalTarget = 0;
      let totalAllocated = 0;
    
      // Slide 3: Physical Target and Fund Allocated for each city
      const cityTargetSlide = pptx.addSlide();
      cityTargetSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
    
      // Add city/municipality and province text
      cityTargetSlide.addText(`${city.col_citymuni.toUpperCase()}, ${province.col_province.toUpperCase()}`, {
        x: 0.5,
        y: 0.5,
        w: '70%',
        fontSize: 24,
        bold: true,
        color: '000991',
        fontFace: 'Arial',
        align: 'center',
      });
    
      // Add "SUMMARY PER PROGRAM" text
      cityTargetSlide.addText('SUMMARY PER PROGRAM', {
        x: 0.5,
        y: 1.0,
        w: '70%',
        fontSize: 20,
        bold: true,
        color: '000991',
        fontFace: 'Arial',
        align: 'center',
      });
    
      // Generate table for Physical Target and Fund Allocated
      const targetTableData = programs.map((program, index) => {
        const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
        const cityProgram = city.programs.find(p => p.program_name === program.program_name) || {};
        const target = cityProgram.total_target || 0;
        const allocated = cityProgram.total_fund_allocated || 0;
    
        // Add to the city-level totals
        totalTarget += parseFloat(target);
        totalAllocated += parseFloat(allocated);
    
        return [
          { text: program.program_name, options: { fontSize: 10, bold: true, align: 'center', fontFace: 'Arial', fill: fillColor } },
          { text: target.toString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
          { text: allocated.toLocaleString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
        ];
      });
    
      // Add the total row to the table data for each city
      const totalRowTarget = [
        { text: 'Total', options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
        { text: totalTarget.toString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
        { text: totalAllocated.toLocaleString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
      ];
    
      // Add table headers
      const targetTableHeader = [
        { text: 'Program', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Physical Target', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Fund Allocated (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
      ];
    
      // Add the table to the slide with program-wise and total rows
      cityTargetSlide.addTable([targetTableHeader, ...targetTableData, totalRowTarget], {
        x: 1.0,
        y: 1.5,
        w: 8,
        h: 3.5,
        colW: [2.5, 2, 2],
        border: { pt: 0.5, color: 'FFFFFF' },
        fontSize: 12,
        fontFace: 'Arial',
      });
    
      // Now you can add a similar process for Slide 4 to handle Physical Served and Fund Utilized totals
    
      // Initialize totals for the city for the utilization table
      let totalServed = 0;
      let totalUtilized = 0;
    
      // Slide 4: Physical Served and Fund Utilized for each city
      const cityUtilizationSlide = pptx.addSlide();
      cityUtilizationSlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };
    
      // Add city/municipality and province text
      cityUtilizationSlide.addText(`${city.col_citymuni.toUpperCase()}, ${province.col_province.toUpperCase()}`, {
        x: 0.5,
        y: 0.5,
        w: '70%',
        fontSize: 24,
        bold: true,
        color: '000991',
        fontFace: 'Arial',
        align: 'center',
      });
    
      // Add "SUMMARY PER PROGRAM" text
      cityUtilizationSlide.addText('SUMMARY PER PROGRAM', {
        x: 0.5,
        y: 1.0,
        w: '70%',
        fontSize: 20,
        bold: true,
        color: '000991',
        fontFace: 'Arial',
        align: 'center',
      });
    
      // Generate table for Physical Served and Fund Utilized
      const utilizationTableData = programs.map((program, index) => {
        const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
        const cityProgram = city.programs.find(p => p.program_name === program.program_name) || {};
        const served = cityProgram.total_physical_served || 0;
        const utilized = cityProgram.total_fund_utilized || 0;
    
        // Add to the city-level totals
        totalServed += parseFloat(served);
        totalUtilized += parseFloat(utilized);
    
        return [
          { text: program.program_name, options: { fontSize: 10, bold: true, align: 'center', fontFace: 'Arial', fill: fillColor } },
          { text: served.toString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
          { text: utilized.toLocaleString(), options: { fontSize: 10, align: 'center', fontFace: 'Arial', fill: fillColor } },
        ];
      });
    
      // Add the total row to the table data for each city
      const totalRowUtilization = [
        { text: 'Total', options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
        { text: totalServed.toString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
        { text: totalUtilized.toLocaleString(), options: { bold: true, fontSize: 10, align: 'center', fontFace: 'Arial', fill: 'FFD700' } },
      ];
    
      // Add table headers for utilization
      const utilizationTableHeader = [
        { text: 'Program', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Physical Served', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
      ];
    
      // Add the table to the slide with program-wise and total rows
      cityUtilizationSlide.addTable([utilizationTableHeader, ...utilizationTableData, totalRowUtilization], {
        x: 1.0,
        y: 1.5,
        w: 8,
        h: 3.5,
        colW: [2.5, 2, 2],
        border: { pt: 0.5, color: 'FFFFFF' },
        fontSize: 12,
        fontFace: 'Arial',
      });
    });
    
  });
    // Last Slide: Summary of all Cities in the Province (Target and Allocation)
    const summarySlide1 = pptx.addSlide();
    summarySlide1.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

    summarySlide1.addText('SUMMARY PER MUNICIPALITY', {
      x: 0.5,
      y: 0.5, w: '70%', fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
    });


    const summaryTableHeader1 = [
      [
        { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
        { text: 'TARGET', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
      ],
      [
        { text: 'Municipality', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Physical', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Fund Allocated (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
      ]
    ];

    const rows1 = [];
    let totalPhysicalTarget = 0;
    let totalFundAllocated = 0;

    Object.values(province.districts || {}).forEach(district => {
      if (district.district) {
        rows1.push([
          { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 10, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
        ]);
      }

      district.cities.forEach((city, index) => {
        const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
        const target = city.total_target || 0;
        const allocated = city.total_allocation || 0;

        totalPhysicalTarget += target;
        totalFundAllocated += allocated;

        rows1.push([
          { text: city.col_citymuni, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
          { text: target.toString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } },
          { text: allocated.toLocaleString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } }
        ]);
      });
    });

    rows1.push([
      { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
      { text: totalPhysicalTarget.toString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
      { text: totalFundAllocated.toLocaleString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } }
    ]);

    summarySlide1.addTable([...summaryTableHeader1, ...rows1], {
      x: 1.0, // Adjust x-position as needed
      y: 1.0, // Adjust y-position as needed
      w: 7,   // Reduced width of the entire table
      h: 3.5, // Height of the table
      colW: [2.5, 2, 2], // Adjust column widths: [Municipality, Physical Target, Fund Allocated]
      rowH: 0.1, // Adjust row height
      fontSize: 14, 
      border: { pt: 1, color: 'FFFFFF' }, 
      fill: { color: 'FFFFFF' } // Optional: adjust fill for table background
    });

    // Similar for utilization summary
    const summarySlide2 = pptx.addSlide();
    summarySlide2.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

    summarySlide2.addText('SUMMARY PER MUNICIPALITY', {
      x: 0.5,
      y: 0.5, w: '70%', fontSize: 24, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
    });

    const summaryTableHeader2 = [
      [
        { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
        { text: 'SERVED', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
      ],
      [
        { text: 'Municipality', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Physical Served', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
        { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 12, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
      ]
    ];

    const rows2 = [];
    let totalPhysicalServed = 0;
    let totalFundUtilized = 0;

    Object.values(province.districts || {}).forEach(district => {
      if (district.district) {
        rows2.push([
          { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 10, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
        ]);
      }

      district.cities.forEach((city, index) => {
        const fillColor = index % 2 === 0 ? 'BDE0FE' : 'DDEFFA';
        const served = city.total_physical || 0;
        const utilized = city.total_utilization || 0;

        totalPhysicalServed += served;
        totalFundUtilized += utilized;

        rows2.push([
          { text: city.col_citymuni, options: { fontSize: 10, align: 'left', fontFace: 'Arial', fill: fillColor } },
          { text: served.toString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } },
          { text: utilized.toLocaleString(), options: { fontSize: 10, align: 'right', fontFace: 'Arial', fill: fillColor } }
        ]);
      });
    });

    rows2.push([
      { text: 'TOTAL', options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
      { text: totalPhysicalServed.toString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } },
      { text: totalFundUtilized.toLocaleString(), options: { bold: true, fontSize: 10, align: 'right', fontFace: 'Arial', fill: 'FFD700' } }
    ]);

    summarySlide2.addTable([...summaryTableHeader2, ...rows2], {
      x: 1.0, // Adjust x-position as needed
      y: 1.0, // Adjust y-position as needed
      w: 7,   // Reduced width of the entire table
      h: 3.5, // Height of the table
      colW: [2.5, 2, 2], // Adjust column widths: [Municipality, Physical Target, Fund Allocated]
      rowH: 0.1, // Adjust row height
      fontSize: 14, 
      border: { pt: 1, color: 'FFFFFF' }, 
      fill: { color: 'FFFFFF' } // Optional: adjust fill for table background
    });
  });

  // Save the PowerPoint file
  pptx.writeFile({ fileName: 'Province_Brief_Report by Area.pptx' });
};

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

    // Initialize arrays to store total allocations and utilizations per program
    const programAllocations = {};
    const programUtilizations = {};

    programs.forEach(program => {
      // Last Slide: Summary of all Cities in the Province (Target and Allocation)
      const summarySlide1 = pptx.addSlide();
      summarySlide1.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

      // Add Program Name Header
      summarySlide1.addText(` ${program.program_name}`, {
        x: 0.9,
        y: 0.5, w: '60%', fontSize: 20, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
      });

      summarySlide1.addText(`${province.col_province.toUpperCase()}`, {
        x: 0.9,
        y: 1.0, w: '60%', fontSize: 16, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
      });

      // Add the program logo
      summarySlide1.addImage({
        path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
        x: 0.5, y: 0.5, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
      });

      const summaryTableHeader1 = [
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

      const rows1 = [];
      let totalPhysicalTarget = 0;
      let totalFundAllocated = 0;

      Object.values(province.districts || {}).forEach(district => {
        if (district.district) {
          rows1.push([
            { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 8, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
          ]);
        }

        district.cities.forEach((city, index) => {
          const fillColor = index % 2 === 0 ? 'F0F8FF' : 'E6E6FA';
          const target = city.total_target || 0;
          const allocated = city.total_allocation || 0;

          totalPhysicalTarget += target;
          totalFundAllocated += allocated;

          // Store total allocations and utilizations per program
          programAllocations[program.program_name] = (programAllocations[program.program_name] || 0) + allocated;
          programUtilizations[program.program_name] = (programUtilizations[program.program_name] || 0) + city.total_utilization || 0;

          rows1.push([
            { text: city.col_citymuni, options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: fillColor } },
            { text: target.toString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } },
            { text: allocated.toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } }
          ]);
        });
      });

      rows1.push([
        { text: 'TOTAL', options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
        { text: totalPhysicalTarget.toString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
        { text: totalFundAllocated.toLocaleString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } }
      ]);

      summarySlide1.addTable([...summaryTableHeader1, ...rows1], {
        x: 0.5, y: 1.5, w: 7.5, colW: [3.5, 1.5, 2.5], h: 3.5, border: { pt: 1, color: 'FFFFFF' }
      });

      // Similar for utilization summary
      const summarySlide2 = pptx.addSlide();
      summarySlide2.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

      summarySlide2.addText(` ${program.program_name}`, {
        x: 0.9,
        y: 0.5, w: '60%', fontSize: 20, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
      });

      summarySlide2.addText(`${province.col_province.toUpperCase()}`, {
        x: 0.9,
        y: 1.0, w: '60%', fontSize: 16, bold: true, color: '000991', fontFace: 'Arial', align: 'center'
      });

      // Add the program logo
      summarySlide2.addImage({
        path: `${window.location.origin}/${program.program_logo}`, // Assuming program_logo holds the image file name
        x: 0.5, y: 0.5, w: 1.0, h: 1.0 // Adjust x, y, width, and height as necessary
      });

      const summaryTableHeader2 = [
        [
          { text: `${province.col_province.toUpperCase()}`, options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 1 } },
          { text: 'UTILIZATION', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial', colspan: 2 } }
        ],
        [
          { text: 'Municipality', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
          { text: 'Physical', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
          { text: 'Fund Utilized (Php)', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
        ]
      ];

      const rows2 = [];
      let totalUtilizationPhysical = 0;
      let totalFundUtilized = 0;

      Object.values(province.districts || {}).forEach(district => {
        if (district.district) {
          rows2.push([
            { text: `${getOrdinal(district.district)} Congressional District`, options: { bold: true, fontSize: 8, align: 'left', color: '0000FF', fontFace: 'Arial', colspan: 3 } }
          ]);
        }

        district.cities.forEach((city, index) => {
          const fillColor = index % 2 === 0 ? 'F0F8FF' : 'E6E6FA';
          const utilized = city.total_utilization || 0;

          totalUtilizationPhysical += city.total_target || 0; // Assuming you want to sum the targets for utilization
          totalFundUtilized += utilized; // Add utilized amount

          rows2.push([
            { text: city.col_citymuni, options: { fontSize: 8, align: 'left', fontFace: 'Arial', fill: fillColor } },
            { text: city.total_target || 0, options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } },
            { text: utilized.toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial', fill: fillColor } }
          ]);
        });
      });

      rows2.push([
        { text: 'TOTAL', options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
        { text: totalUtilizationPhysical.toString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } },
        { text: totalFundUtilized.toLocaleString(), options: { bold: true, fontSize: 8, align: 'right', fontFace: 'Arial', fill: 'ADD8E6' } }
      ]);

      summarySlide2.addTable([...summaryTableHeader2, ...rows2], {
        x: 0.5, y: 1.5, w: 7.5, colW: [3.5, 1.5, 2.5], h: 3.5, border: { pt: 1, color: 'FFFFFF' }
      });
    });

    // Summary Slide for All Programs - Allocation
const allocationSummarySlide = pptx.addSlide();
allocationSummarySlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

allocationSummarySlide.addText(`ALLOCATION SUMMARY FOR ${province.col_province.toUpperCase()}`, { 
  x: '0%', y: '5%', w: '100%', fontSize: 26, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
});

// Prepare data for the allocation summary table
const allocationTableHeader = [
  [
    { text: 'PROGRAM NAME', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
    { text: 'TOTAL ALLOCATED (Php)', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
  ]
];

const allocationRows = Object.keys(programAllocations).map(programName => {
  return [
    { text: programName, options: { fontSize: 8, align: 'left', fontFace: 'Arial' } },
    { text: programAllocations[programName].toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } }
  ];
});

allocationSummarySlide.addTable([...allocationTableHeader, ...allocationRows], {
  x: 0.5, y: 1.5, w: 7.5, colW: [4.0, 2.5], h: 3.5, border: { pt: 1, color: 'FFFFFF' }
});

// Summary Slide for All Programs - Utilization
const utilizationSummarySlide = pptx.addSlide();
utilizationSummarySlide.background = { path: `${window.location.origin}/ppd-images/ppt-table.png` };

utilizationSummarySlide.addText(`UTILIZATION SUMMARY FOR ${province.col_province.toUpperCase()}`, { 
  x: '0%', y: '5%', w: '100%', fontSize: 26, bold: true, color: '00072D', fontFace: 'Arial', align: 'center' 
});

// Prepare data for the utilization summary table
const utilizationTableHeader = [
  [
    { text: 'PROGRAM NAME', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } },
    { text: 'TOTAL UTILIZED (Php)', options: { bold: true, fontSize: 10, align: 'center', color: 'FFFFFF', fill: '0070C0', fontFace: 'Arial' } }
  ]
];

const utilizationRows = Object.keys(programUtilizations).map(programName => {
  return [
    { text: programName, options: { fontSize: 8, align: 'left', fontFace: 'Arial' } },
    { text: programUtilizations[programName].toLocaleString(), options: { fontSize: 8, align: 'right', fontFace: 'Arial' } }
  ];
});

utilizationSummarySlide.addTable([...utilizationTableHeader, ...utilizationRows], {
  x: 0.5, y: 1.5, w: 7.5, colW: [4.0, 2.5], h: 3.5, border: { pt: 1, color: 'FFFFFF' }
});
});
  // Save the presentation
  pptx.writeFile('Program_Report.pptx');
};

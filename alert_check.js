const cron = require('node-cron');
const { exec } = require('child_process');

cron.schedule('*/5 * * * *', () => {
  exec('php check_alerts.php', (err, stdout, stderr) => {
    if (err) {
      console.error('Error running PHP script:', err);
      return;
    }
    console.log('PHP output:', stdout);
  });
});
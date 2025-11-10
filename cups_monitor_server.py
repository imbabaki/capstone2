from flask import Flask, jsonify
import subprocess
import re
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

def get_print_job_status(job_id=None):
    """
    Get the status of CUPS print jobs.
    Returns information about the most recent or specified job.
    """
    try:
        # Get all jobs from CUPS
        result = subprocess.run(['lpstat', '-o'],
                              capture_output=True,
                              text=True,
                              timeout=2)

        if result.returncode != 0 or not result.stdout.strip():
            # No active jobs
            return {
                'status': 'completed',
                'job_id': None,
                'pages_printed': 0,
                'total_pages': 0,
                'progress': 100,
                'message': 'No active print jobs'
            }

        # Parse lpstat output
        # Format: printer-job_id username priority date time [processing/pending/etc]
        jobs = result.stdout.strip().split('\n')

        if job_id:
            # Find specific job
            job_line = None
            for job in jobs:
                if f'-{job_id} ' in job:
                    job_line = job
                    break
            if not job_line:
                return {
                    'status': 'completed',
                    'job_id': job_id,
                    'pages_printed': 0,
                    'total_pages': 0,
                    'progress': 100,
                    'message': 'Job completed or not found'
                }
        else:
            # Get most recent job (last line)
            job_line = jobs[-1]

        # Extract job ID
        match = re.search(r'-(\d+)\s+', job_line)
        if match:
            current_job_id = match.group(1)
        else:
            current_job_id = 'unknown'

        # Determine if job is processing
        is_processing = 'processing' in job_line.lower()

        # Get detailed job info
        detail_result = subprocess.run(['lpstat', '-l', '-o'],
                                      capture_output=True,
                                      text=True,
                                      timeout=2)

        # Try to extract page information
        pages_printed = 0
        total_pages = 0

        if detail_result.returncode == 0:
            detail_output = detail_result.stdout

            # Look for page information in the detailed output
            # Format varies, but typically shows something like:
            # "queued for printer since Mon 10 Nov 2025 10:30:00 AM PST"
            # or shows completed pages

            # Try to find total pages from job
            total_match = re.search(r'(\d+)\s+page', detail_output)
            if total_match:
                total_pages = int(total_match.group(1))

        # Calculate progress
        if is_processing:
            # Job is actively printing
            progress = 50  # Assume halfway if we can't determine exact progress
            status = 'printing'
            message = 'Printing in progress...'
        else:
            # Job is queued/pending
            progress = 10
            status = 'pending'
            message = 'Print job queued...'

        return {
            'status': status,
            'job_id': current_job_id,
            'pages_printed': pages_printed,
            'total_pages': total_pages,
            'progress': progress,
            'message': message,
            'is_active': True
        }

    except subprocess.TimeoutExpired:
        return {
            'status': 'error',
            'job_id': None,
            'pages_printed': 0,
            'total_pages': 0,
            'progress': 0,
            'message': 'Timeout checking print status',
            'is_active': False
        }
    except Exception as e:
        return {
            'status': 'error',
            'job_id': None,
            'pages_printed': 0,
            'total_pages': 0,
            'progress': 0,
            'message': f'Error: {str(e)}',
            'is_active': False
        }

@app.route('/print/status', methods=['GET'])
@app.route('/print/status/<job_id>', methods=['GET'])
def print_status(job_id=None):
    """Get the current print job status"""
    status = get_print_job_status(job_id)
    return jsonify(status)

@app.route('/print/check-complete', methods=['GET'])
@app.route('/print/check-complete/<job_id>', methods=['GET'])
def check_complete(job_id=None):
    """
    Check if a specific print job is complete.
    Returns true if job is no longer in the queue.
    """
    try:
        result = subprocess.run(['lpstat', '-o'],
                              capture_output=True,
                              text=True,
                              timeout=2)

        if result.returncode != 0 or not result.stdout.strip():
            # No jobs in queue = completed
            return jsonify({
                'completed': True,
                'message': 'Print queue is empty'
            })

        if job_id:
            # Check if specific job is in queue
            jobs = result.stdout
            if f'-{job_id} ' in jobs:
                return jsonify({
                    'completed': False,
                    'message': f'Job {job_id} is still in queue'
                })
            else:
                return jsonify({
                    'completed': True,
                    'message': f'Job {job_id} completed'
                })
        else:
            # If there are any jobs, not complete
            return jsonify({
                'completed': False,
                'message': 'Print jobs still in queue'
            })

    except Exception as e:
        return jsonify({
            'completed': False,
            'message': f'Error: {str(e)}'
        }), 500

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'running',
        'service': 'CUPS Monitor'
    })

if __name__ == "__main__":
    print("🖨️  CUPS Monitor Server Running on 0.0.0.0:5007")
    app.run(host="0.0.0.0", port=5007, debug=False)

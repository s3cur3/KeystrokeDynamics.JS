###########################################################################
# evaluation-script.R                                                     #
#                                                                         #
# Comparing Anomaly Detectors for Keystroke Biometrics                    #
# Evaluation Proceedure                                                   #
# R Script                                                                #
#                                                                         #
# by: Kevin Killourhy                                                     #
# date: May 19, 2009                                                      #
###########################################################################

library( MASS );
library( ROCR );
library( stats );

# The euclideanTrain and euclideanScore functions comprise the
# Euclidean anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector.  This mean vector is returned as the
# detection model.  During scoring, the detector takes the detection
# model and a new set of password-timing vectors (also encoded as rows
# in a matrix) and calculates the squared Euclidean distance between
# the mean vector and each of the new password-timing vectors.  These
# scores are returned in a vector whose length is equal to the number
# of password-timing vectors in the scoring matrix.

euclideanScore <- function( dmod, YScore ) {
  p <- length( dmod$mean );
  n <- nrow( YScore );

  if( ncol(YScore) != p ) stop("Training/test feature length mismatch ");
  
  meanMatrix <- matrix( dmod$mean, byrow=TRUE, nrow=n, ncol=p );

  scores <- rowSums( ( YScore - meanMatrix )^2 );

  return( scores );
}


# Load in the training model
datafile <- 'r/dmod.csv';
if( ! file.exists(datafile) )
  stop( "Detection model file ", datafile, " does not exist");
detection.model <- read.csv(datafile, nrows=2, header=TRUE)

# Load in "this" attempt's timing array
#YScore <- ???

# Get the probability that this is the real user
#score <- euclideanScore( detection.model, YScore )

# Return to PHP

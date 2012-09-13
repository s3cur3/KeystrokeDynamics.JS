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

euclideanScore <- function( detection.model, YScore ) {
  p <- length( detection.model ); #p <- length( detection.model$mean );
  n <- nrow( YScore );
  print( paste("Number of columns: ", p, "  Number of rows: ", n, sep = " ") );

  if( ncol(YScore) != p ) stop("Training/test feature length mismatch ");
  
  #meanMatrix <- matrix( detection.model$mean, byrow=TRUE, nrow=n, ncol=p );
  meanMatrix <- matrix( detection.model, byrow=TRUE, nrow=n, ncol=p );
  
  scores <- rowSums( ( YScore - detection.model )^2 );

  return( scores );
}


# Load in the training model
detection.model.csv <- 'r/dmod.csv';
if( ! file.exists(detection.model.csv) ) {
    stop( "Detection model file ", detection.model.csv, " does not exist");
}
detection.model <- as.matrix( read.csv( detection.model.csv,
                                        nrows=2,
                                        header=TRUE,
                                        stringsAsFactors=FALSE ) );

# Load in "this" attempt's timing array
current.attempt.file <- 'r/current_attempt.csv';
if( ! file.exists(current.attempt.file) ) {
    stop( "Current attempt data file ", current.attempt.file, " does not exist");
}
YScore <- as.matrix( read.csv( current.attempt.file,
                               nrows=2,
                               header=TRUE,
                               stringsAsFactors=FALSE ) );

# Get the "score" (distance between the model and this attempt
score <- euclideanScore( detection.model, YScore );

# Scale the score based on the number of keys in the input
# This is the per-key average deviation from the model (squared)
deviation.avg <- score / length( detection.model );

# An arbitrarily chosen maximum acceptable average deviation
# Chosen because this is about double my worst score
deviation.max <- 7000;

prob.imposter <- deviation.avg / deviation.max
if( prob.imposter > 1.0 ) {
  prob.imposter <- 1.0
}

# Return to PHP
write( "Probability you are an imposter:", "" );
write( prob.imposter, "" )

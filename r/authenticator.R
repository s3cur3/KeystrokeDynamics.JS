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

# This script demonstrates how the DSL-StrongPassword data set can be
# used to train and evaluate three anomaly detectors, reproducing the
# results for those anomaly detectors reported in:

#   K.S. Killourhy and R.A. Maxion. "Comparing Anomaly Detectors for
#   Keystroke Biometrics," in Proceedings of the 39th Annual
#   Dependable Systems and Networks Conference, pages 125-134,
#   Estoril, Lisbon, Portugal, June 29-July 2, 2009.  IEEE Computer
#   Society Press, Los Alamitos, California. 2009.

# The script is shared in order to promote open scientific discourse,
# and to encourage other researchers to reproduce and expand upon our
# results (e.g., by evaluating their own, novel anomaly detectors)
# using our data and procedures.

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
detection.model <- ???

# Load in "this" attempt's timing array
YScore <- ???

# Get the probability that this is the real user
score <- euclideanScore( detection.model, YScore )

# Return to PHP
